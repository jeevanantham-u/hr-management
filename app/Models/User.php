<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'email',
        'username',
        'password',
        'role_id',
        'is_active',
    ];
    protected array $hidden = ['password'];

    /**
     * Example relationship — requires a `posts` table with a `user_id` column.
     *
     *   $user = User::find(1);
     *   $user->posts(); // array of Post models
     */
    // public function posts(): array
    // {
    //     return $this->hasMany(\App\Models\Post::class, 'user_id');
    // }

    /**
     * Example relationship — requires a `roles` table.
     *
     *   $user->role(); // Role model or null
     */
    // public function role(): mixed
    // {
    //     return $this->belongsTo(\App\Models\Role::class, 'role_id');
    // }
}
