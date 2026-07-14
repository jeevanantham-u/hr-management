<?php
namespace App\Core;

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

    public static function all()
    {
        $instance = new static();
        $row = Database::select("SELECT * FROM {$instance->table} WHERE is_active = 1");
        return $row;
    }

    public static function find($id)
    {
        $instance = new static();
        $row = Database::selectOne(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );
        //  echo json_encode($row);
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

        // $fields = [];
        // $values = ['id' => $id];
        // foreach ($data as $key => $value) {
        //     $fields[] = "{$key} = :{$key}";
        //     $values[$key] = $value;
        // }
        // $setString = implode(', ', $fields)

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

    // public static function where(string $column, $operator, $value = null)
    // {
    //     if ($value == null) {
    //         $value = $operator;
    //         $operator = '=';
    //     }
    //     $instance = new static();
    //     return new QueryBuilder($instance->table, $instance->primaryKey, static::class)
    //         ->where($column, $operator, $value);
    // }

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
    }

    // public function where(string $column, $operator, $value = null)
    // {
    //     if ($value == null) {
    //         $value = $operator;
    //         $operator = '=';
    //     }

    //     $this->wheres = [$column, $operator];
    //     $this->bindings = $value;

    //     return $this;
    // }

}