<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\UserService;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function index()
    {
        try {
            $users = $this->userService->getAllUsers();
            $this->success($users, "User retrieved successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->route('id');
            $user = $this->userService->findById($id);

            $this->success($user->toArray(), "User retrieved successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->body();
            $user = $this->userService->createUser($data);

            $this->success($user, "User created successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $data = $request->body();
            $id = $request->route('id');
            $user = $this->userService->updateUser($id, $data);

            $this->success($user, "User updated successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }

    public function destroy(Request $request){
        try {
            $id = $request->route('id');
            $user = $this->userService->deleteUser($id);

            $this->success($user, "User deleted successfully.", 200);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), null, 500);
        }
    }
}

// catch (\Throwable $e) {
//             echo $e->getMessage();
//             echo "<br>";
//             echo $e->getFile();
//             echo "<br>";
//             echo $e->getLine();
//             var_dump($e);
//         }