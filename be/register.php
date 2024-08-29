<?php
require_once "config.php";

try {
  // Check if the request method is POST
  if (!$_SERVER['REQUEST_METHOD'] == "POST") {
    throw new Error("Method not allowed");
  }

  // Get the form data
  $name = $_POST["name"];
  $email = $_POST["email"];
  $pwd = $_POST["pwd"];

  // Throw error if any field is empty
  if (!$name || !$email || !$pwd) {
    throw new Exception("All fields are required");
  }

  // Validate email address
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception("Invalid email address");
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

  // Instantiate the DB class
  $db = new DB('sqlite.db');

  // Make sure email is unique
  if ($db->read('email', $email)) {
    throw new Exception("Email already exists");
  }

  // Create a new user
  $user = $db->create($name, $email, $pwd);

  // Redirect to the main page
  header("Location: ../fe/login.html");
} catch (Throwable $e) {
  echo("<script> alert(" . $e->getMessage() . ") </script>");
  header("Location: ../fe/register.html");
}