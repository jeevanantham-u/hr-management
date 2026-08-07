<?php
namespace App\Models;
use App\Core\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'dob',
        'gender',
        'joining_date',
        'department_id',
        'designation',
        'manager_id',
        'salary',
        'address',
        'status'
    ];
}