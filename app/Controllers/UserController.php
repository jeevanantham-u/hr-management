<?php
namespace App\Controllers;

use App\Models\User;

class UserController
{
    public function index()
    {
        // Instantiate the user model (which connects to the DB via BaseModel)
        $userModel = new User();
        $users = $userModel->getAllUsers();

        echo json_encode(['data' => $users],  JSON_PRETTY_PRINT);
    }

    public function show($id)
    {
        $userModel = new User();
        $users = $userModel->findById($id);

        echo json_encode(['data' => $users],  JSON_PRETTY_PRINT);
    }
}