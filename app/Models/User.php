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
        'is_active'
    ];
    protected array $hidden = ['password'];
}