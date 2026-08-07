<?php
namespace App\Models;

use App\Core\Model;

class Payroll extends Model
{
    protected $table = 'payroll';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'status',
        'generated_at'
    ];
}