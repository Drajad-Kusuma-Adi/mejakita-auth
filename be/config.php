<?php

class DB {
  private $conn;
  private $secret;

  /**
   * Encrypt a value using the app secret
   * @param string $val value to encrypt
   * @return string encrypted value
   */
  private function enc(string $val) {
    return hash_hmac('sha256', $val, $this->secret);
  }

  /**
   * Create a new DB instance
   * @param string $dbpath path to sqlite database
   * @return void
   */
  public function __construct(string $dbpath) {
    try {
      // Connect to sqlite database and set error handling verbosity
      $this->conn = new PDO('sqlite:'.$dbpath);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Read app secret from .env file
      $this->secret = getenv('APP_SECRET');
    } catch (Throwable $e) {
      // Handle any errors that occur during the connection process
      header('Location: ../fe/login.html');
    }
  }

  /**
   * Create a new user in the database
   * @param string $name user full name
   * @param string $email user email
   * @param string $pwd user password
   * @return array user data
   */
  public function create(string $name, string $email, string $pwd) {
    try {
      // Generate random ID and token for the user
      $id = bin2hex(random_bytes(16));

      // Create a new user in the database
      $stmt = $this->conn->prepare('INSERT INTO users (id, name, email, pwd, token) VALUES (:id, :name, :email, :pwd, :token)');
      $stmt->execute(array('id' => $id, ':name' => $name, ':email' => $email, ':pwd' => $this->enc($pwd, $this->secret), ':token' => null));

      // Return the user data
      return $this->read($id);
    } catch (Throwable $e) {
      header('Location: ../fe/login.html');
    }
  }

  /**
   * Read a user from the database
   * @param string $id user id
   * @return array user data
   */
  public function read(string $id) {
    try {
      // Read a user data from the database according to ID
      $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
      $stmt->execute(array(':id' => $id));

      // Return the user data
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      header('Location: ../fe/login.html');
    }
  }

  /**
   * Update a user's detail in the database
   *
   * @param string $id user id
   * @param string $col column to update
   * @param mixed $val new value for update
   * @return array new user data
   */
  public function update(string $id, string $col, string $val) {
    try {
      // Update the user's detail in the database
      $stmt = $this->conn->prepare("UPDATE users SET $col = :val WHERE id = :id");
      $stmt->execute(array(':val' => $val, ':id' => $id));

      // Return the updated user data
      return $this->read($id);
    } catch (Throwable $e) {
      header('Location: ../fe/login.html');
    }
  }

  /**
   * Delete a user from the database
   * @param string $id user id
   * @return boolean whether or not the operation was successful
   */
  public function destroy($userId) {
    try {
      // Delete a user from the database
      $stmt = $this->conn->prepare('DELETE FROM users WHERE id = :id');
      $stmt->execute(array(':id' => $userId));

      return true;
    } catch (Throwable $e) {
      header('Location: ../fe/login.html');
      return false;
    }
  }
}
