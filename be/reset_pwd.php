<?php
require_once "config.php";

try {
  // Check if the request method is POST
  if (!$_SERVER['REQUEST_METHOD'] == "POST") {
    throw new Exception("Method not allowed");
  }

  // Instantiate the DB class
  $db = new DB('sqlite.db');

  // Verify that email exists
  $user = $db->read('email', $email);
  if (!$user) {
    throw new Exception("Email doesn't exists.");
  }

  // Check if pwd field exists
  $pwd = $_POST["pwd"];
  if (empty($pwd)) {
    header("Location: ../fe/reset_pass.html");
    exit;
  }

  // Validate password
  switch (true) {
    case (strlen($pwd) < 8):
        // Handle password length less than 8 characters
        throw new Exception("Password must be at least 8 characters long.");
        break;
    case (!preg_match('/[0-9]/', $pwd)):
        // Handle password without a digit
        throw new Exception ("Password must contain at least one digit.");
        break;
    case (!preg_match('/[\W_]/', $pwd)):
        // Handle password without a special character
        throw new Exception("Password must contain at least one special character.");
        break;
  }

  // Change password
  $db->update($user['id'], 'pwd', $pwd);

  // Redirect to the login page
  header("Location: ../fe/login.html");
} catch (Throwable $e) {
  echo("<script> alert(" . $e->getMessage() . ") </script>");
  header("Location: ../fe/forgot_pass.html");
}