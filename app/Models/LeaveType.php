<?php

namespace App\Models;

use App\Core\Model;

class LeaveType extends Model{
    protected $table = 'leave_types';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'description',
        'max_days'
    ];
}