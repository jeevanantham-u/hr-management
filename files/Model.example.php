<?php


/**
 * Base model providing an Eloquent-like API on top of Database/QueryBuilder.
 *
 * Static query methods (where, orderBy, whereIn, ...) are forwarded to a
 * fresh QueryBuilder via __callStatic, so you can write:
 *
 *   User::where('role_id', 1)->orderBy('created_at', 'DESC')->get();
 *   User::find(5);
 *   User::whereIn('role_id', [1, 2])->paginate(20);
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];
    protected array $original = [];
    protected array $timestamps = ['created_at', 'updated_at'];

    /** @var array<string, array> cache of loaded relations for this instance */
    protected array $relations = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
    }

    // ------------------------------------------------------------------
    // Query entry points
    // ------------------------------------------------------------------

    public static function query(): QueryBuilder
    {
        $instance = new static();

        $builder = new QueryBuilder(
            $instance->table,
            $instance->primaryKey,
            static::class
        );

        // Model::delete() soft-deletes via is_active, so scope reads to
        // active rows by default unless the caller opts into withTrashed().
        return $builder;
    }

    /**
     * Forward static calls like User::where(...), User::orderBy(...),
     * User::whereIn(...), User::count(), etc. to a fresh QueryBuilder.
     */
    public static function __callStatic($method, $args)
    {
        $builder = static::query();

        if (!method_exists($builder, $method)) {
            throw new \BadMethodCallException(
                'Call to undefined method ' . static::class . "::{$method}()"
            );
        }

        return $builder->{$method}(...$args);
    }

    public static function all(): array
    {
        return static::query()->orderBy((new static())->primaryKey)->get();
    }

    public static function find(mixed $id): ?static
    {
        return static::query()->find($id);
    }

    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new \RuntimeException(static::class . " with primary key '{$id}' not found.");
        }

        return $model;
    }

    /**
     * Create and persist a new model in one call.
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    // ------------------------------------------------------------------
    // Persistence
    // ------------------------------------------------------------------

    protected function fillableAttributes(): array
    {
        return array_intersect_key($this->attributes, array_flip($this->fillable));
    }

    public function insert(): bool
    {
        $fillable = $this->fillableAttributes();

        if (in_array('created_at', $this->timestamps, true)) {
            $fillable['created_at'] = date('Y-m-d H:i:s');
        }
        if (in_array('updated_at', $this->timestamps, true)) {
            $fillable['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(',', array_keys($fillable));
        $placeholders = implode(',', array_fill(0, count($fillable), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $id = Database::insert($sql, array_values($fillable));

        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            foreach ($fillable as $k => $v) {
                $this->attributes[$k] = $v;
            }
            $this->original = $this->attributes;
            return true;
        }

        return false;
    }

    public function update(): int
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return 0;
        }

        $fillable = $this->fillableAttributes();

        if (in_array('updated_at', $this->timestamps, true)) {
            $fillable['updated_at'] = date('Y-m-d H:i:s');
        }

        if (empty($fillable)) {
            return 0;
        }

        $sets = implode(',', array_map(fn ($col) => "{$col} = ?", array_keys($fillable)));
        $sql = "UPDATE {$this->table} SET {$sets} WHERE {$this->primaryKey} = ?";
        $values = array_merge(array_values($fillable), [$this->attributes[$this->primaryKey]]);

        $affected = Database::update($sql, $values);

        foreach ($fillable as $k => $v) {
            $this->attributes[$k] = $v;
        }
        $this->original = $this->attributes;

        return $affected;
    }

    /**
     * Soft delete: sets is_active = 0, matching the existing convention
     * where reads are scoped to is_active = 1 by default.
     */
    public function delete(): int
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            return 0;
        }

        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE {$this->primaryKey} = ?";
        $affected = Database::delete($sql, [$this->attributes[$this->primaryKey]]);

        $this->attributes['is_active'] = 0;
        return $affected;
    }

    public function save(): bool|int
    {
        return isset($this->attributes[$this->primaryKey])
            ? $this->update()
            : $this->insert();
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    public function isDirty(): bool
    {
        return $this->attributes !== $this->original;
    }

    // ------------------------------------------------------------------
    // Relationships (basic, eager-load-free; resolved on access)
    // ------------------------------------------------------------------

    /**
     * $comment->belongsTo(User::class, 'user_id')
     */
    protected function belongsTo(string $relatedClass, ?string $foreignKey = null, string $ownerKey = 'id'): mixed
    {
        $foreignKey ??= $this->guessForeignKey($relatedClass);
        $value = $this->attributes[$foreignKey] ?? null;

        if ($value === null) {
            return null;
        }

        return $relatedClass::query()->where($ownerKey, $value)->first();
    }

    /**
     * $user->hasMany(Post::class, 'user_id')
     *
     * @return array<int, static>
     */
    protected function hasMany(string $relatedClass, ?string $foreignKey = null, string $localKey = 'id'): array
    {
        $foreignKey ??= $this->guessForeignKey(static::class);
        $localValue = $this->attributes[$localKey] ?? null;

        if ($localValue === null) {
            return [];
        }

        return $relatedClass::query()->where($foreignKey, $localValue)->get();
    }

    /**
     * $user->hasOne(Profile::class, 'user_id')
     */
    protected function hasOne(string $relatedClass, ?string $foreignKey = null, string $localKey = 'id'): mixed
    {
        $foreignKey ??= $this->guessForeignKey(static::class);
        $localValue = $this->attributes[$localKey] ?? null;

        if ($localValue === null) {
            return null;
        }

        return $relatedClass::query()->where($foreignKey, $localValue)->first();
    }

    protected function guessForeignKey(string $class): string
    {
        $short = substr($class, strrpos($class, '\\') + 1);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $short)) . '_id';
    }

    // ------------------------------------------------------------------
    // Array / attribute access
    // ------------------------------------------------------------------

    public function toArray(): array
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset($name): bool
    {
        return isset($this->attributes[$name]);
    }
}
