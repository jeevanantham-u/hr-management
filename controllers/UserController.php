<?php
namespace Api\Controllers;

class UserController {
    
    // Triggered by GET /api/users
    public function index() {
        echo json_encode(["message" => "Fetching all users list"]);
    }

    // Triggered by GET /api/users/{id}
    public function show($id) {
        echo json_encode([
            "message" => "Fetching specific user",
            "requested_user_id" => $id
        ]);
    }

    // Triggered by POST /api/users
    public function store() {
        // Capture JSON request bodies
        $inputData = json_decode(file_get_contents('php://input'), true);
    }
}
