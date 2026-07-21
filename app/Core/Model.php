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
        return static::query()->orderBy('username', 'asc')->get();
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
