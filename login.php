<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<form action="login.php" method="post">
    <label for="username">Username:</label><br>
    <input type="text" id="username" name="username"><br>
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br><br>
    <input type="submit" value="Login">
</form>
<p>Don't have an account? <a href="register.php">Register</a></p>

<?php
include 'connection.php';
$pdo = include 'connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT ID FROM users WHERE login = ? AND pass = ?');
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Login successful 🎉";
        session_start(); // Запуск сессии и создание уникального токена
        $_SESSION['user_id'] = $user['ID'];
        header("Location: index.php");
        exit();
    } else {
        echo "Invalid username or password 😞";
    }
}
?>
</body>
</html>