<?php

namespace App\Core;

class QueryBuilder
{
    protected string $table;
    protected string $primaryKey;
    protected string $modelClass;
    protected array $columns = ['*'];
    protected array $orderBy = [];
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

    public function orderBy(string $column, string $direction = 'ASC')
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    protected function buildSelectSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . " FROM {$this->table}";

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
}