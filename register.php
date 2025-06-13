<?php
require 'db.php';

// Check if an admin already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
$adminExists = $stmt->fetchColumn() > 0;

if ($adminExists) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);

    header("Location: login.php");
    exit;
}
?>
<html>
<head>
    <title>Admin register</title>
     <style>
        /* General Reset and Body Styling */
body {
    margin: 0;
    padding: 0;
    font-family: "Segoe UI", sans-serif;
    background: #f4f6f9;
    color: #2c3e50;
}

/* Container */
.container {
    max-width: 300px;
    margin: 60px auto;
    padding: 40px;
    background: navajowhite;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

/* Headings */
h2, h3 {
    text-align: center;
    margin-bottom: 25px;
    color: #1a252f;
}

/* Forms */
form {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
input[type="text"],
input[type="password"]{
    padding: 12px;
    border: 1px solid #dcdde1;
    border-radius: 8px;
    font-size: 15px;
    width: 100%;
    background-color: #f9f9f9;
    transition: border 0.2s ease-in-out;
}

input:focus, select:focus {
    outline: none;
    border-color: #3498db;
    background-color: #ffffff;
}

button {
    padding: 12px;
    background: #3498db;
    border: none;
    color: white;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
}

button:hover {
    background: #2980b9;
}
</style>
</head>
<body>
<h2>Register Admin</h2>
<div class="container">
<form method="post">
    Username: <input type="text" name="username" required>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
    <p style="text-align: center;">Already have admin account? <a href="login.php">Login</a></p>
</form>
</div>
</body>
</html>
