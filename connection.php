<?php
$host = 'localhost'; // адрес сервера
$db = 'practice'; // имя базы данных
$user = 'root'; // имя пользователя
$pass = ''; // пароль

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Ошибка соединения с базой данных: " . $e->getMessage());
}
?>