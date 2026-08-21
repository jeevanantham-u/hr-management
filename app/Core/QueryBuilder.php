<?php

namespace App\Core;

use Closure;
class QueryBuilder
{
    protected string $table;
    protected string $primaryKey;
    protected string $modelClass;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected array $bindings = [];

    public function __construct($table, $primaryKey, $modelClass = Model::class)
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->modelClass = $modelClass;
    }

    public function select(string|array $columns)
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where(string|Closure $columns, mixed $operatorOrValue, mixed $value = null)
    {
        return $this->addwhere('AND', $columns, $operatorOrValue, $value);
    }

    protected function addwhere(string $boolean, string|Closure $column, mixed $operatorOrValue, mixed $value)
    {
        [$operator, $value] = $this->normalizeOperatorValue($operatorOrValue, $value);
        return $this->whereBasic($boolean, $column, $operator, $value);
    }

    /**
     * Distinguish where('col', $value) from where('col', $operator, $value).
     */

    protected function normalizeOperatorValue(mixed $operatorOrValue, mixed $value)
    {
        if (func_num_args() === 2 && $value === null && !is_string($operatorOrValue)) {
            return ['=', $operatorOrValue];
        }

        if ($value === null && is_string($operatorOrValue) && $this->looksLikeOperator($operatorOrValue)) {
            return ['=', $operatorOrValue];
        }

        if ($value === null) {
            return [is_string($operatorOrValue) && $this->looksLikeOperator($operatorOrValue) ? $operatorOrValue : '=', $operatorOrValue];
        }

        return [$operatorOrValue, $value];
    }

    protected function looksLikeOperator(string $value)
    {
        return in_array(strtoupper($value), [
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
            'IS NOT NULL'
        ], true);
    }

    protected function whereBasic(string $boolean, string $column, string $operator, mixed $value)
    {
        $this->pushWhere($boolean, "$column $operator ?", [$value]);
        return $this;
    }

    protected function pushWhere(string $boolean, string $sql, array $bindings)
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

    public function orderBy(string $column, string $direction = 'ASC')
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    protected function buildSelectSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' ', $this->wheres);
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        return $sql;
    }

    public function getRaw()
    {
        return Database::select($this->buildSelectSql(), $this->bindings);
    }

    public function get()
    {
        $rows = $this->getRaw();
        $modelClass = $this->modelClass;

        return array_map(fn($row) => new $modelClass($row), $rows);
    }

    public function first()
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    public function count()
    {
        $sql = "SELECT COUNT(*) AS count FROM {$this->table}";

        if (!empty($this->wheres)) {
            $whereClause = implode(array_map(
                fn($where) => "{$where[0]} {$where[1]}",
                $this->wheres
            ));
            $sql .= " WHERE $whereClause";
        }

        $results = Database::selectOne($sql, $this->bindings);
        return (int) ($result['count'] ?? 0);
    }

    public function find(mixed $id): mixed
    {
        return $this->where($this->primaryKey, $id)->first();
        return 0;
    }
}