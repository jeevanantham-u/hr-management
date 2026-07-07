<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index()
    {
        try {
            $users = $this->userModel->getAllUsers();

            $this->success($users, "Users retrieved successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->route('id');
            $user = $this->userModel->findById($id);

            $this->success($user, "User retrieved successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $userInfo = [
                'username' => $request->body('username'),
                'email' => $request->body('email'),
                'password' => $request->body('password'),
                'role_id' => $request->body('role_id')
            ];

            $rules = [
                'username' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'role_id' => 'required'
            ];

            $is_valid = $this->validate($userInfo, $rules);
            if (empty($is_valid)) {
                $user = $this->userModel->createUser($userInfo);

                $this->success($user, "User created successfully.", 200);
            }

            $this->error('Somthing went wrong', $is_valid, 500);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $userInfo = $request->body();
            $id = $request->route('id');

            $user = $this->userModel->editUserById($id, $userInfo);

            $this->success($user, "User updated successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }

    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->route('id');

            $user = $this->userModel->deleteById($id);

            $this->success($user, "User deleted  successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }
}