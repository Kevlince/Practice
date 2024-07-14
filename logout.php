<?php
session_start();
session_destroy(); // Разрушаем все данные сессии
header("Location: login.php"); // Перенаправляем пользователя на страницу входа
exit();
?>