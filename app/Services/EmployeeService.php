<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;

class EmployeeService
{
    private EmployeeRepository $repository;

    public function __construct()
    {
        $this->repository = new EmployeeRepository();
    }

    public function getAllEmployee()
    {
        return $this->repository->all();
    }

    public function getEmployee($id)
    {
        return $this->repository->find($id);
    }

    public function createEmployee($data)
    {
        if (empty($data['employee_code'])) {
            $data['employee_code'] = $this->generateEmployeeCode();
        }
    
        return $this->repository->create($data);
    }

    public function updateEmployee($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteEmployee($id)
    {
        return $this->repository->delete($id);
    }

    public function searchEmployee($query)
    {
        return $this->repository->search($query);
    }

    public function getEmployeesBydepartment($departmentId)
    {
        return $this->repository->getByDepartment($departmentId);
    }

    public function getTotalEmployees()
    {
        return $this->repository->getTotalCount();
    }

    public function validateEmployeeData()
    {
    }

    public function generateEmployeeCode()
    {
        $year = date('Y');
        $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "EMP-$year-$randomNumber";
    }
}