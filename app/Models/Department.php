<?php

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'code',
        'description'
    ];
}