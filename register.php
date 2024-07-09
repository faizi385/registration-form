<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$phone = $_POST['phone'];

$action = $_POST['action'];

if ($action === 'Sign Up') {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "User already registered!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $phone);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful!";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
} elseif ($action === 'Login') {
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['message'] = "Login successful!";
        } else {
            $_SESSION['message'] = "Invalid email or password.";
        }
    } else {
        $_SESSION['message'] = "No user found with this email.";
    }

    $stmt->close();
}

$conn->close();

header("Location: login.php");
exit();
?>
