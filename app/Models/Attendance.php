<?php

namespace App\Models;

use App\Core\Model;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'check_in',
        'check_out',
        'total_hours',
        'attendance_date'
    ];
}