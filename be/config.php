<?php

class DB {
  private $conn;
  private $secret;

  /**
   * Encrypt a value using the app secret
   * @param string $val value to encrypt
   * @return string encrypted value
   * @deprecated use PHP built-in password_hash() instead
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
      throw $e;
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
      // $stmt->execute(array('id' => $id, ':name' => $name, ':email' => $email, ':pwd' => $this->enc($pwd, $this->secret), ':token' => null));
      $stmt->execute(array('id' => $id, ':name' => $name, ':email' => $email, ':pwd' => password_hash($pwd, PASSWORD_DEFAULT), ':token' => null));

      // Return the user data
      return $this->read('id', $id);
    } catch (Throwable $e) {
      throw $e;
    }
  }

  /**
   * Index all users in the database
   */
  public function index() {
    try {
      // Read all users from the database
      $stmt = $this->conn->prepare('SELECT * FROM users');
      $stmt->execute();

      // Return the user data
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      throw $e;
    }
  }

  /**
   * Read a user from the database
   * @param string $col column to read
   * @param string $val value to read
   * @return array user data
   */
  public function read(string $col, string $val) {
    try {
      // Read a user data from the database according to column and value
      $stmt = $this->conn->prepare('SELECT * FROM users WHERE '.$col.' = :val LIMIT 1');
      $result = $stmt->execute(array(':val' => $val));

      // Throw an error if no user is found
      if (!$result) {
        throw new Exception('User not found');
      }

      // Return the user data
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      throw $e;
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
      // Encrypt value if $col is 'pwd'
      if ($col === 'pwd') {
        // $val = $this->enc($val);
        $val = password_hash($val, PASSWORD_DEFAULT);
      }

      // Update the user's detail in the database
      $stmt = $this->conn->prepare("UPDATE users SET $col = :val WHERE id = :id");
      $stmt->execute(array(':val' => $val, ':id' => $id));

      // Return the updated user data
      return $this->read('id', $id);
    } catch (Throwable $e) {
      throw $e;
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
      throw $e;
      return false;
    }
  }
}
