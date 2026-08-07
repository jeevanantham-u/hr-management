<?php

namespace App\Models;

use App\Core\Model;

class Leave extends Model
{
    protected $table = 'leaves';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'remarks'
    ];
}