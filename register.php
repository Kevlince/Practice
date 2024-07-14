<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
<h2>Register</h2>
<form action="register.php" method="post">
    <label for="newusername">Username:</label><br>
    <input type="text" id="newusername" name="newusername"><br>
    <label for="newpassword">Password:</label><br>
    <input type="password" id="newpassword" name="newpassword"><br><br>
    <input type="submit" value="Register">
</form>
<p>Already have an account? <a href="login.php">Login</a></p>

<?php
include 'connection.php';
$pdo = include 'connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newusername = $_POST['newusername'];
    $newpassword = $_POST['newpassword'];

    // Проверяем, существует ли уже пользователь с таким же именем
    $stmt = $pdo->prepare('SELECT ID FROM users WHERE username = ?');
    $stmt->execute([$newusername]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Пользователь с таким именем уже существует! Пожалуйста, выберите другое имя. ❌";
    } else {
        // Если пользователя с таким именем не существует, тогда регистрируем его
        $stmt = $pdo->prepare('INSERT INTO users (username, password, status) VALUES (?, ?, 1)');
        $stmt->execute([$newusername, $newpassword]);
        echo "Пользователь успешно зарегистрирован! 🎉";
    }
}
?>
</body>
</html>