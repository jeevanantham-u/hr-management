<?php


use App\Core\Database;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];
    protected array $timestamps = ['created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();

        return new QueryBuilder(
            $instance->table,
            $instance->primaryKey,
            static::class
        );
    }

    public static function all()
    {
        $instance = new static();
        $rows = static::query()->where('is_active', 1)->get();

        $result = [];

        foreach ($rows as $row) {
            foreach ($instance->hidden as $field) {
                unset($row[$field]);
            }

            $result[] = $row;
        }

        return $result;
    }

    public static function find($id)
    {
        $instance = new static();
        $row = Database::selectOne(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );

        return $row ? new static($row) : null;
    }

    public function insert(): bool
    {
        $fillable = array_intersect_key(
            $this->attributes,
            array_flip($this->fillable)
        );

        if (in_array('created_at', $this->timestamps)) {
            $fillable['created_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(',', array_keys($fillable));
        $placeholders = implode(',', array_fill(0, count($fillable), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $id = Database::insert($sql, array_values($fillable));

        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            return true;
        }

        return false;
    }

    public function update(): int
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        $fillable = array_intersect_key(
            $this->attributes,
            array_flip($this->fillable)
        );

        if (in_array('updated_at', $this->timestamps)) {
            $fillable['updated_at'] = date('Y-m-d H:i:s');
        }

        $sets = implode(',', array_map(fn($col) => "$col = ?", array_keys($fillable)));

        $sql = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?";

        $values = array_merge(array_values($fillable), [$this->attributes[$this->primaryKey]]);

        echo json_encode([$sets, $sql, $values], JSON_PRETTY_PRINT);

        return Database::update($sql, $values);
    }

    public function delete(): int
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE {$this->primaryKey} = ?";

        return Database::delete($sql, [$this->attributes[$this->primaryKey]]);
    }

    public function save()
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        }
        return $this->insert();
    }

    public function toArray()
    {
        return array_diff_key(
            $this->attributes,
            array_flip($this->hidden)
        );
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $this->attributes[$name] = $value;
    }
}

class QueryBuilder
{
    private string $table;
    private string $primaryKey;
    private string $modelClass;
    private array $wheres = [];
    private array $bindings = [];
    private ?int $limit;
    private ?int $offset;
    private array $orderBy = [];

    public function __construct(string $table, $primaryKey = 'id', $modelClass = 'App\\Core\\Model')
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->modelClass = $modelClass;
        $this->limit = null;
        $this->offset = null;
    }

    /**
     * Add a basic WHERE clause.
     *
     * Examples:
     *  Model::query()->where('id', 1)->get();
     *  Model::query()->where('role_id', 'IN', [1,2])->get();
     *  Model::query()->where('email', 'LIKE', '%test%')->get();
     */
    public function where(string $column, $operatorOrValue, $value = null): self
    {
        $operator = '=';

        if ($value === null) {
            // where('col', $value)
            $value = $operatorOrValue;
        } else {
            // where('col', '!=', $value) OR where('col', 'IN', [..])
            $operator = (string) $operatorOrValue;
        }

        $operatorUpper = strtoupper(trim($operator));

        if ($operatorUpper === 'IN' || $operatorUpper === 'NOT IN') {
            if (!is_array($value)) {
                throw new \InvalidArgumentException("Value for {$operatorUpper} must be an array");
            }

            if (count($value) === 0) {
                // col IN () is invalid SQL, so force false/true depending on operator
                $this->wheres[] = ($operatorUpper === 'IN') ? '1 = 0' : '1 = 1';
                return $this;
            }

            $placeholders = implode(',', array_fill(0, count($value), '?'));
            $this->wheres[] = "{$column} {$operatorUpper} ({$placeholders})";
            foreach ($value as $v) {
                $this->bindings[] = $v;
            }
            return $this;
        }

        if (is_array($value) && $operatorUpper !== 'IN' && $operatorUpper !== 'NOT IN') {
            throw new \InvalidArgumentException("Non-IN where() expects a scalar value");
        }

        $this->wheres[] = "{$column} {$operatorUpper} ?";
        $this->bindings[] = $value;
        
        return $this;
    }

    public function get()
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        return Database::select($sql, $this->bindings);
    }

    public function limit(int $limit){
        $this->limit = $limit;
    }
}

