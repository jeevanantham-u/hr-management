<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    private EmployeeService $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    public function index(Request $request)
    {
        if (!$request->isAuthenticated()) {
            $this->error('Unauthorized', null, 401);
        }

        $employees = $this->service->getAllEmployee();

        $this->success($employees, 'Employees retrived successful', 200);
    }
    public function store(Request $request)
    {
        $data = [
            'first_name' => $request->body('first_name'),
            'last_name' => $request->body('last_name'),
            'email' => $request->body('email'),
            'phone' => $request->body('phone'),
            'dob' => $request->body('dob'),
            'gender' => $request->body('gender'),
            'joining_date' => $request->body('joining_date'),
            'department_id' => $request->body('department_id'),
            'designation' => $request->body('designation'),
            'manager_id' => $request->body('manager_id'),
            'salary' => $request->body('salary'),
            'address' => $request->body('address'),
            'status' => 'Active'
        ];

        $errors = $this->validate($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'dob' => 'required',
            'gender' => 'required',
            'joining_date' => 'required',
            'department_id' => 'required',
            'designation' => 'required',
            'manager_id' => 'required',
            'salary' => 'required',
            'address' => 'required',
        ]);

        if (!empty($errors)) {
            $this->error('Validation failed', $errors, 422);
        }

        $result = $this->service->createEmployee($data);

        $this->success($result, "Employee created successfully.", 200);
    }
    public function show(Request $request)
    {
        $id = $request->route('id');
        $employee = $this->service->getEmployee($id);

        $this->success($employee, "Employee created successfully.", 200);
    }
    public function update(Request $request)
    {
        $id = $request->route('id');
        $data = $request->body();

        $result = $this->service->updateEmployee($id, $data);

        $this->success($result, 'Employee detailes updated', 201);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');

        $result = $this->service->deleteEmployee($id);
        $this->success($result, "Employee deleted successfully.");
    }
    public function search(Request $request)
    {
        $query = $request->route('query');

        $result = $this->service->searchEmployee($query);
        $this->success($result, "Employee match found.", 200);
    }
}