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
        $users = $this->userService->getAllUsers();
        $this->success($users, "User retrieved successfully.", 200);
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