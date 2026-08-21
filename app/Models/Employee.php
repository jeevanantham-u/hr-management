<?php
namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected string $table = 'employees';
    protected string $primaryKey = 'id';
    protected array $fillable = [
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

    public static function findByCode($code): ?self
    {
        return self::where('employee_code', $code)->first();
    }
    public function department(): Department
    {
        return Department::find($this->attributes['department_id'] ?? null);
    }
    public function manager()
    {
        return self::find($this->attributes['manager_id'] ?? null);
    }
    public function getFullName()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}