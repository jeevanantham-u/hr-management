<?php

namespace App\Models;

use App\Core\Model;

class Shift extends Model
{
    protected $table = 'shifts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time'
    ];
}