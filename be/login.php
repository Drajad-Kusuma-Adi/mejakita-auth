<?php
error_reporting(0);
require_once "config.php";

$output = array();
$res_code = 200;

// Start output buffering
ob_start();

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        $res_code = 405;
        throw new Exception("Method not allowed");
    }

    // Get the form data
    $email = $_POST["email"];
    $pwd = $_POST["pwd"];

    // Throw error if any field is empty
    if (!$email || !$pwd) {
        $res_code = 422;
        throw new Exception("All fields are required");
    }

    // Throw error if email or password is not a string
    if (!is_string($email) || !is_string($pwd)) {
        $res_code = 422;
        throw new Exception("Email and password must be strings");
    }

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $res_code = 422;
        throw new Exception("Invalid email format");
    }

    // Instantiate the DB class
    $db = new DB('sqlite.db');

    // Check if user exists
    $user = $db->read('email', $email);
    if (!$user) {
        $res_code = 404;
        throw new Exception("User not found");
    }

    // Validate password
    switch (true) {
        case (strlen($pwd) < 8):
            // Handle password length less than 8 characters
            $res_code = 422;
            throw new Exception("Password must be at least 8 characters long.");
            break;
        case (!preg_match('/[0-9]/', $pwd)):
            // Handle password without a digit
            $res_code = 422;
            throw new Exception ("Password must contain at least one digit.");
            break;
        case (!preg_match('/[\W_]/', $pwd)):
            // Handle password without a special character
            $res_code = 422;
            throw new Exception("Password must contain at least one special character.");
            break;
    }

    // Update the user's token
    $newUser = $db->update($user['id'], 'token', bin2hex(random_bytes(16)));

    // Return the user data
    $output = $newUser;
} catch (Throwable $e) {
    $output = array('error' => $e->getMessage());
} finally {
    // Set the content type to JSON
    header('Content-Type: application/json');

    // Output the JSON data
    http_response_code($res_code);
    echo json_encode($output);

    // Flush the output buffer
    ob_end_flush();
}