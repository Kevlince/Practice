<?php
session_start();
if (!isset($_SESSION['user_id'])) { // Если пользователь не авторизован
    header("Location: login.php"); // Перенаправляем его на страницу входа
    exit();
}

error_reporting(E_ERROR | E_PARSE);
// Include the connection file
include 'connection.php';
//$pdo = include 'connection.php';
global $pdo;

// Функция для получения всех избранных постов для текущего пользователя
function getFavoritePosts() {
    global $pdo;
    $stmt = $pdo->prepare('SELECT p.id, p.title, p.text FROM favs f JOIN posts p ON f.p_id = p.id WHERE f.u_id = :userId');
    $stmt->execute(['userId' => 1]); // Замените 1 на реальный ID пользователя
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем все избранные посты
$favoritePosts = getFavoritePosts();

// Обработка действий пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_favorites'])) {
    $stmt = $pdo->prepare('DELETE FROM favs WHERE u_id = :userId');
    $stmt->execute(['userId' => 1]); // Замените 1 на реальный ID пользователя
    header("Location: favorites.php"); // Перенаправление на эту же страницу для обновления списка избранных
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Избранные посты</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        td button {
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
        }
        .clear-favorites {
            background-color: #FF6347; /* красный */
            color: #fff;
        }
        .back-button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<h1>Избранные посты</h1>
<p style="text-align: right;"><a href="logout.php">Выход</a></p>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Заголовок</th>
        <th>Текст</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($favoritePosts as $post):?>
        <tr>
            <td><?php echo $post['id']; ?></td>
            <td><?php echo $post['title']; ?></td>
            <td><?php echo $post['text']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<form action="" method="post">
    <button class="clear-favorites" type="submit" name="clear_favorites">Очистить избранное</button>
</form>
<div class="back-button">
    <a href="index.php">Назад</a>
</div>
</body>
</html>