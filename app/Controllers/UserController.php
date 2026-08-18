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

    public function index(Request $request)
    {
        if (!$request->isAuthenticated()) {
            $this->error('Unauthorized', null, 401);
        }

        $user = $request->user();

        $users = $this->userService->getAllUsers();
        $this->success($users, "All users retrieved successfully.", 200);
    }

    public function store(Request $request)
    {
        $data = $request->body();
        $result = $this->userService->createUser($data);
        $this->success($result, "User created successfully.", 200);
    }

    public function show(Request $request)
    {
        $id = $request->route('id');
        $user = $this->userService->getOneUser($id);
        $this->success($user, "User retrived successfully.", 200);
    }

    public function update(Request $request)
    {
        $id = $request->route('id');
        $body = $request->body();
        $result = $this->userService->editUser($id, $body);
        $this->success($result, "User updated successfully.", 200);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $result = $this->userService->deleteUser($id);
        $this->success($result, "User deleted successfully.", 200);
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