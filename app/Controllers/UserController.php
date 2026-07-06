<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\User;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index()
    {
        // Instantiate the user model (which connects to the DB via BaseModel)
        $users = $this->userModel->getAllUsers();

        echo json_encode(['success' => true, 'data' => $users], JSON_PRETTY_PRINT);
    }

    public function show(Request $request)
    {
        $id = $request->route('id');
        $users = $this->userModel->findById($id);

        echo json_encode(['success' => true, 'data' => $users], JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
        $userInfo = [
            'username' => $request->body('username'),
            'email' => $request->body('email'),
            'password' => $request->body('password'),
            'role_id' => $request->body('role_id')
        ];

        $user = $this->userModel->createUser($userInfo);

        echo json_encode(['success' => true, 'data' => $user], JSON_PRETTY_PRINT);
    }

    public function update(Request $request)
    {
        $userInfo = $request->body();
        $id = $request->route('id');

        $user = $this->userModel->editUserById($id, $userInfo);

        echo json_encode(['success' => true, 'data' => $user], JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request)
    {
        $id = $request->route('id');

        $user = $this->userModel->deleteById($id);

        echo json_encode(['success' => true, 'data' => ["user" => $user]], JSON_PRETTY_PRINT);
    }
}