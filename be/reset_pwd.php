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

  // Instantiate the DB class
  $db = new DB('sqlite.db');

  // Get form data
  $email = $_POST["email"];

  // Throw error if email is not a string
  if (!is_string($email)) {
    $res_code = 422;
    throw new Exception("Email must be a string");
  }

  // Verify that email exists
  $user = $db->read('email', $email);
  if (!$user) {
    $res_code = 404;
    throw new Exception("User not found");
  }

  // Check if pwd field exists
  $pwd = $_POST["pwd"];

  // If password field is empty, return response that email is verified
  // Else, continue with password reset
  if (empty($pwd)) {
    $output = array('email' => $email);
  } else {
    // Throw error if pwd is not a string
    if (!is_string($pwd)) {
      $res_code = 422;
      throw new Exception("Password must be a string");
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
    $newUser = $db->update($user['id'], 'pwd', $pwd);

    // Return the updated user data
    $output = $newUser;
  }
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