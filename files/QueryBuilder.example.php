<?php


use Closure;
use InvalidArgumentException;

/**
 * Fluent, chainable query builder in the spirit of Laravel's Eloquent builder.
 *
 * Usage (via Model, which forwards static calls here):
 *   User::where('role_id', 1)->orderBy('created_at', 'DESC')->get();
 *   User::where('email', 'LIKE', '%@gmail.com')->first();
 *   User::whereIn('role_id', [1, 2])->count();
 *   User::query()->where(fn($q) => $q->where('a', 1)->orWhere('b', 2))->get();
 */
class QueryBuilder
{
    protected string $table;
    protected string $primaryKey;
    protected string $modelClass;

    /** @var string[] */
    protected array $columns = ['*'];

    /** @var string[] built SQL fragments, e.g. "email = ?" or "(a = ? OR b = ?)" */
    protected array $wheres = [];

    /** @var array bound values, in the same order as the wheres/joins that reference them */
    protected array $bindings = [];

    protected ?int $limit = null;
    protected ?int $offset = null;

    /** @var string[] e.g. ["created_at DESC"] */
    protected array $orderBy = [];

    protected bool $withTrashed = false;
    protected bool $onlyTrashed = false;
    protected string $softDeleteColumn = 'is_active';

    public function __construct(string $table, string $primaryKey = 'id', string $modelClass = Model::class)
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->modelClass = $modelClass;
    }

    // ------------------------------------------------------------------
    // Column selection
    // ------------------------------------------------------------------

    public function select(array|string $columns): static
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    // ------------------------------------------------------------------
    // WHERE clauses
    // ------------------------------------------------------------------

    /**
     * where('col', $value)
     * where('col', '!=', $value)
     * where('col', 'IN', [...])
     * where('col', 'BETWEEN', [$min, $max])
     * where(function ($q) { $q->where(...)->orWhere(...); })   -- grouped
     */
    public function where(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): static
    {
        return $this->addWhere('AND', $column, $operatorOrValue, $value);
    }

    public function orWhere(string|Closure $column, mixed $operatorOrValue = null, mixed $value = null): static
    {
        return $this->addWhere('OR', $column, $operatorOrValue, $value);
    }

    protected function addWhere(string $boolean, string|Closure $column, mixed $operatorOrValue, mixed $value): static
    {
        // Grouped where: where(fn($q) => ...)
        if ($column instanceof Closure) {
            $nested = new static($this->table, $this->primaryKey, $this->modelClass);
            $column($nested);

            if (empty($nested->wheres)) {
                return $this;
            }

            $sql = '(' . implode(' ', $nested->wheres) . ')';
            $this->pushWhere($boolean, $sql, $nested->bindings);
            return $this;
        }

        [$operator, $value] = $this->normalizeOperatorValue($operatorOrValue, $value);
        $operator = strtoupper(trim($operator));

        return match ($operator) {
            'IN', 'NOT IN' => $this->whereInInternal($boolean, $column, $value, $operator === 'NOT IN'),
            'BETWEEN' => $this->whereBetweenInternal($boolean, $column, $value, false),
            'NOT BETWEEN' => $this->whereBetweenInternal($boolean, $column, $value, true),
            'NULL', 'IS NULL' => $this->whereNullInternal($boolean, $column, false),
            'NOT NULL', 'IS NOT NULL' => $this->whereNullInternal($boolean, $column, true),
            default => $this->whereBasic($boolean, $column, $operator, $value),
        };
    }

    /**
     * Distinguish where('col', $value) from where('col', $operator, $value).
     */
    protected function normalizeOperatorValue(mixed $operatorOrValue, mixed $value): array
    {
        if (func_num_args() === 2 && $value === null && !is_string($operatorOrValue)) {
            // where('col', $value) where $value isn't an operator string (e.g. array, int, null)
            return ['=', $operatorOrValue];
        }

        if ($value === null && is_string($operatorOrValue) && !$this->looksLikeOperator($operatorOrValue)) {
            // where('col', 'somestring') -> treat the string as the value, operator defaults to '='
            return ['=', $operatorOrValue];
        }

        if ($value === null) {
            // where('col', $value) with $value === null intentionally, or where('col','=', null) missing 3rd arg
            return [is_string($operatorOrValue) && $this->looksLikeOperator($operatorOrValue) ? $operatorOrValue : '=', $operatorOrValue];
        }

        return [$operatorOrValue, $value];
    }

    protected function looksLikeOperator(string $value): bool
    {
        return in_array(strtoupper(trim($value)), [
            '=',
            '!=',
            '<>',
            '<',
            '<=',
            '>',
            '>=',
            'LIKE',
            'NOT LIKE',
            'IN',
            'NOT IN',
            'BETWEEN',
            'NOT BETWEEN',
            'NULL',
            'IS NULL',
            'NOT NULL',
            'IS NOT NULL',
        ], true);
    }

    protected function whereBasic(string $boolean, string $column, string $operator, mixed $value): static
    {
        if (is_array($value)) {
            throw new InvalidArgumentException("Operator '{$operator}' does not accept an array value for column '{$column}'.");
        }

        $this->pushWhere($boolean, "{$this->quoteIdentifier($column)} {$operator} ?", [$value]);
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        return $this->whereInInternal('AND', $column, $values, false);
    }

    public function orWhereIn(string $column, array $values): static
    {
        return $this->whereInInternal('OR', $column, $values, false);
    }

    public function whereNotIn(string $column, array $values): static
    {
        return $this->whereInInternal('AND', $column, $values, true);
    }

    protected function whereInInternal(string $boolean, string $column, mixed $values, bool $not): static
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException("whereIn/whereNotIn expects an array for column '{$column}'.");
        }

        $keyword = $not ? 'NOT IN' : 'IN';

        if (count($values) === 0) {
            // empty IN() is invalid SQL; short-circuit to a tautology/contradiction
            $this->pushWhere($boolean, $not ? '1 = 1' : '1 = 0', []);
            return $this;
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->pushWhere($boolean, "{$this->quoteIdentifier($column)} {$keyword} ({$placeholders})", array_values($values));
        return $this;
    }

    public function whereNull(string $column): static
    {
        return $this->whereNullInternal('AND', $column, false);
    }

    public function whereNotNull(string $column): static
    {
        return $this->whereNullInternal('AND', $column, true);
    }

    protected function whereNullInternal(string $boolean, string $column, bool $not): static
    {
        $keyword = $not ? 'IS NOT NULL' : 'IS NULL';
        $this->pushWhere($boolean, "{$this->quoteIdentifier($column)} {$keyword}", []);
        return $this;
    }

    public function whereBetween(string $column, array $range): static
    {
        return $this->whereBetweenInternal('AND', $column, $range, false);
    }

    public function whereNotBetween(string $column, array $range): static
    {
        return $this->whereBetweenInternal('AND', $column, $range, true);
    }

    protected function whereBetweenInternal(string $boolean, string $column, mixed $range, bool $not): static
    {
        if (!is_array($range) || count($range) !== 2) {
            throw new InvalidArgumentException("whereBetween expects an array of exactly 2 values for column '{$column}'.");
        }

        $keyword = $not ? 'NOT BETWEEN' : 'BETWEEN';
        $this->pushWhere($boolean, "{$this->quoteIdentifier($column)} {$keyword} ? AND ?", array_values($range));
        return $this;
    }

    /**
     * Escape hatch for raw SQL fragments. Caller is responsible for using
     * placeholders ('?') and never interpolating untrusted input directly.
     */
    public function whereRaw(string $sql, array $bindings = []): static
    {
        $this->pushWhere('AND', $sql, $bindings);
        return $this;
    }

    protected function pushWhere(string $boolean, string $sql, array $bindings): void
    {
        if (empty($this->wheres)) {
            $this->wheres[] = $sql;
        } else {
            $this->wheres[] = "{$boolean} {$sql}";
        }

        foreach ($bindings as $b) {
            $this->bindings[] = $b;
        }
    }

    // ------------------------------------------------------------------
    // Ordering / pagination
    // ------------------------------------------------------------------

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "{$this->quoteIdentifier($column)} {$direction}";
        return $this;
    }

    public function latest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'ASC');
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function take(int $limit): static
    {
        return $this->limit($limit);
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function skip(int $offset): static
    {
        return $this->offset($offset);
    }

    // ------------------------------------------------------------------
    // Soft-delete scoping
    // Model::delete() sets is_active = 0 instead of removing the row, so
    // reads are scoped to is_active = 1 by default, matching that behavior.
    // ------------------------------------------------------------------

    public function withTrashed(): static
    {
        $this->withTrashed = true;
        return $this;
    }

    public function onlyTrashed(): static
    {
        $this->onlyTrashed = true;
        return $this;
    }

    // ------------------------------------------------------------------
    // Execution
    // ------------------------------------------------------------------

    public function toSql(): array
    {
        return [$this->buildSelectSql(), $this->bindings];
    }

    protected function buildSelectSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . " FROM {$this->table}";

        $clauses = [];
        if (!$this->withTrashed) {
            $clauses[] = $this->onlyTrashed
                ? "{$this->softDeleteColumn} = 0"
                : "{$this->softDeleteColumn} = 1";
        }
        if (!empty($this->wheres)) {
            $clauses[] = implode(' ', $this->wheres);
        }

        if (!empty($clauses)) {
            $sql .= ' WHERE ' . implode(' AND ', array_map(fn($c) => "({$c})", $clauses));
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . (int) $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . (int) $this->offset;
        }

        return $sql;
    }

    /**
     * @return array raw associative-array rows
     */
    public function getRaw(): array
    {
        return Database::select($this->buildSelectSql(), $this->bindings);
    }

    /**
     * @return array<int, object> array of hydrated model instances
     */
    public function get(): array
    {
        $rows = $this->getRaw();
        $modelClass = $this->modelClass;
        return array_map(fn($row) => new $modelClass($row), $rows);
    }

    public function first(): mixed
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function find(mixed $id): mixed
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    public function count(): int
    {
        $original = $this->columns;
        $this->columns = ['COUNT(*) AS aggregate'];
        $sql = $this->buildSelectSql();
        $this->columns = $original;

        $row = Database::selectOne($sql, $this->bindings);
        return (int) ($row['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function pluck(string $column): array
    {
        $rows = $this->select([$column])->getRaw();
        return array_column($rows, $column);
    }

    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $total = (clone $this)->count();

        $items = $this->limit($perPage)->offset(($page - 1) * $perPage)->get();

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    // ------------------------------------------------------------------
    // Bulk mutations
    // ------------------------------------------------------------------

    public function updateAll(array $values): int
    {
        if (empty($values)) {
            return 0;
        }

        $sets = implode(', ', array_map(fn($col) => "{$this->quoteIdentifier($col)} = ?", array_keys($values)));
        $sql = "UPDATE {$this->table} SET {$sets}";
        $bindings = array_values($values);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' ', $this->wheres);
            $bindings = array_merge($bindings, $this->bindings);
        }

        return Database::update($sql, $bindings);
    }

    public function deleteAll(): int
    {
        // Soft delete, consistent with Model::delete().
        return $this->updateAll([$this->softDeleteColumn => 0]);
    }

    public function forceDeleteAll(): int
    {
        $sql = "DELETE FROM {$this->table}";
        $bindings = $this->bindings;

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' ', $this->wheres);
        }

        return Database::delete($sql, $bindings);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function quoteIdentifier(string $column): string
    {
        // Basic guard against accidental injection through dynamic column names.
        // Allows table.column, letters, numbers, underscores only.
        if (!preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)?$/', $column)) {
            throw new InvalidArgumentException("Invalid column name: {$column}");
        }

        return $column;
    }
}
