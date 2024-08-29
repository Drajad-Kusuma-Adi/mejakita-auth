<?php
require_once "config.php";

try {
    if (!$_SERVER['REQUEST_METHOD'] == "POST") {
        throw new Exception("Method not allowed");
    }

    // VALIDASI EMAIL SESUAI FORMAT
    $valid_email = "user@gmail.com";
    $valid_pass = "12345503";

    $email = $_POST["email"];
    $pass = $_POST["password"];

    $emailError = "";
    $passError = "";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format";
    }

    if ($email == "" || $pass == "") {
        // FIELD BELUM DIISI

        if (empty($_POST["email"])) {
            $emailError = "Email is required.";
        }

        if (empty($_POST["password"])) {
            $passwordError = "Password is required.";
        }
    }

    if (strlen($pass) >= 8) {
        // FUNGSI 2
        $passError = "Password must be at least 8 characters long.";
    } else if (!preg_match('/[0-9]/', $pass)) {
        $passError = "Password harus mengandung setidaknya satu angka.";
    } else if (!preg_match('/[\W_]/', $pass)) {
        $passError = "Password harus mengandung setidaknya satu karakter khusus.";
    }

    if (empty($passError) && empty($emailError)) {
        // Instantiate the DB class
        $db = new DB('sqlite.db');

        // Make sure email&pass exist
        $user = $db->read('(email, pass)', "($email, $pass)");
        if ($user) {
            // Save user data
            $db->update($user['id'], 'token', bin2hex(random_bytes(16)));
        } else {
            throw new Exception("Email/Pass not correct");
        }

        // Redirect to the main page
        header("Location: ../fe/main.html");
    }

} catch (Throwable $e) {
    echo("<script> alert(" . $e->getMessage() . ") </script>");
    header("Location: ../fe/login.html");
}
// 1. VALIDASI EMAIL DIISI SESUAI FORMAT -> type="email" (input) --DONE
// 2. PASWORD WAJIB DIISI DENGAN SYARAT MINIMAL 8 KARAKTER, angka, karakter khusus -> minlength="8" (input) 
// 3. TAMPILAN KESALAHAN JIKA INPUT TIDAK VALID EMAIL DAN PASSWORD-> /login?err=validfield --DONE
// 4. TAMPILAN KESALAHAN JIKA FIELD BELUM DIISI (EMAIL DAN PASSWOORD)-> required (input) --DONE
// 5. CHECKBOX INGAT SAYA
