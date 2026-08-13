<?php

namespace App\Core;

use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];
    protected array $original = [];
    protected array $timestamps = ['created_at', 'updated_at'];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

    public static function query(): QueryBuilder
    {
        $instance = new static();
        $builder = new QueryBuilder(
            $instance->table,
            $instance->primaryKey,
            static::class
        );

        return $builder;
    }

    public static function all(): array
    {
        return static::query()->where('is_active', 1)->orderBy('username', 'asc')->get();
    }

    public static function find($id)
    {
        return static::query()->find($id);
    }

    public static function create($data): static
    {
        $model = new static($data);
        $model->save();
        return $model;
    }

    public static function where(string $column, $operator, $value = null): QueryBuilder
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $instance = new static();
        return (new QueryBuilder($instance->table, $instance->primaryKey, static::class))
            ->where($column, $operator, $value);
    }

    protected function fillableAttributes()
    {
        return array_intersect_key($this->attributes, array_flip($this->fillable));
    }

    public function insert()
    {
        $fillable = $this->fillableAttributes();

        date_default_timezone_set("Asia/Kolkata");

        if (in_array('created_at', $this->timestamps, true)) {
            $fillable['created_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(', ', array_keys($fillable));
        $placeholders = implode(', ', array_fill(0, count($fillable), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $id = Database::insert($sql, array_values($fillable));
        if ($id) {
            $this->attributes['id'] = $id;

            foreach ($fillable as $k => $v) {
                $this->attributes[$k] = $v;
            }
            $this->original = $this->attributes;
            return true;
        }
        return false;
    }

    public function update()
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return 0;
        }

        $fillable = $this->fillableAttributes();

        date_default_timezone_set("Asia/Kolkata");

        if (in_array('updated_at', $this->timestamps, true)) {
            $fillable['updated_at'] = date('Y-m-d H:i:s');
        }

        if (empty($fillable)) {
            return 0;
        }

        $sets = implode(", ", array_map(fn($col) => "{$col} = ?", array_keys($fillable)));
        $sql = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?";
        $values = array_merge(array_values($fillable), [$this->attributes[$this->primaryKey]]);

        $affected = Database::update($sql, $values);

        foreach ($fillable as $k => $v) {
            $this->attributes[$k] = $v;
        }

        $this->original = $this->attributes;

        return $affected;
    }

    public function delete()
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return 0;
        }

        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE {$this->primaryKey} = ?";

        return Database::delete($sql, [$this->attributes[$this->primaryKey]]);
    }

    public function save()
    {
        return isset($this->attributes[$this->primaryKey]) ?
            $this->update() :
            $this->insert();
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $method = 'set' . ucfirst($name) . 'Attribute';

        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
