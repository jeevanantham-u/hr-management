<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Employee;

class EmployeeRepository
{
    public function all()
    {
        return Employee::all();
    }
    public function find($id)
    {
        return Employee::find($id);
    }
    public function findByCode($code)
    {
        return Employee::findByCode($code);
    }
    public function create($data)
    {
        $new = Employee::create($data);
        return $new;
    }
    public function update($id, $data)
    {
        $employee = $this->find($id);

        if (!$employee) {
            return false;
        }

        foreach ($data as $k => $v) {
            $employee->$k = $v;
        }

        return $employee->save();
    }
    public function delete($id)
    {
        $employee = $this->find($id);

        if (!$employee || $employee->staus == 'Inactive') {
            return false;
        }

        $employee->status = 'Inactive';

        return $employee->save();
    }
    public function search($query)
    {
        return Database::select(
            "SELECT * FROM employees WHERE
            first_name LIKE ? OR
            last_name LIKE ? OR
            email LIKE ? OR
            employee_code LIKE ?",
            ["%$query%", "%$query%", "%$query%", "%$query%"]
        );
    }
    public function getByDepartment(int $departmentId): array
    {
        return Employee::where('department_id', $departmentId)->get();
    }
    public function getTotalCount()
    {
        return Employee::where('status', 'Active')->count();
    }
}