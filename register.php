<?php
session_start();
require 'db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            $success = "Account created successfully. You can now log in.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Attendance System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
<div class="auth-wrap">
    <form class="auth-card" method="POST" action="register.php">
        <div class="auth-icon"><i class="ti ti-user-plus"></i></div>
        <div class="auth-title">Create account</div>
        <div class="auth-subtitle">Sign up to start tracking attendance</div>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="auth-success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <label for="name">Full name</label>
        <input type="text" id="name" name="name" placeholder="Your full name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="name@company.com" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 6 characters" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>

        <button type="submit">Register</button>

        <div class="auth-footer">Already have an account? <a href="login.php">Log in</a></div>
    </form>
</div>
</body>
</html>