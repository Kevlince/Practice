<?php
session_start();
if (!isset($_SESSION['user_id'])) { // Если пользователь не авторизован
    header("Location: login.php"); // Перенаправляем его на страницу входа
    exit();
}

error_reporting(E_ERROR | E_PARSE);
include 'connection.php';
//$pdo = include 'connection.php';

// Функция для получения всех постов
function getPosts() {
    global $pdo;
    $url = 'https://jsonplaceholder.typicode.com/posts';

    // Получение всех постов
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);

    $postsFromApi = json_decode($result, true);

    // Подготовка выражения на проверку есть ли пост с данным ID в базе
    $stmtSelect = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE id = :postId');

    // Подготовка выражения для вставки нового поста
    $stmtInsert = $pdo->prepare('INSERT INTO posts (id, title, content) VALUES (:id, :title, :content)');

    // Массив, для хранения финального списка постов
    $posts = [];

    // Обработка вытащенных постов
    foreach ($postsFromApi as $post) {
        $postId = $post['id'];

        // Провеска есть ли пост с данным ID в базе
        $stmtSelect->execute(['postId' => $postId]);
        $exists = (bool) $stmtSelect->fetchColumn();

        // Если поста нет в базе, добавить его
        if (!$exists) {
            $stmtInsert->execute([
                'id' => $postId,
                'title' => $post['title'],
                'content' => $post['body'],
            ]);
        }

        // Добавление поста в массив вне зависимости от того есть он в базе или нет
        $posts[] = [
            "id" => $postId,
            "title" => $post['title'],
            "content" => $post['body'],
        ];
    }

    return $posts;
}

// Функция для проверки, добавлен ли пост в избранное
function isFavorite($postId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM favs WHERE u_id = :userId AND p_id = :postId');
    $stmt->execute(['userId' => 1, 'postId' => $postId]); // Замените 1 на реальный ID пользователя
    return (bool) $stmt->fetchColumn();
}

// Функция для добавления поста в избранное
function addToFavorites($postId) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO favs (u_id, p_id) VALUES (:userId, :postId)');
    $stmt->execute(['userId' => 1, 'postId' => $postId]); // Замените 1 на реальный ID пользователя
}

// Функция для удаления поста из избранного
function removeFromFavorites($postId) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM favs WHERE u_id = :userId AND p_id = :postId');
    $stmt->execute(['userId' => 1, 'postId' => $postId]); // Заменить 1 на реальный ID пользователя
}

// Получаем все посты
$posts = getPosts();

// Обработка действий пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_favorites'])) {
        $postId = $_POST['add_to_favorites'];
        addToFavorites($postId);
    } elseif (isset($_POST['remove_from_favorites'])) {
        $postId = $_POST['remove_from_favorites'];
        removeFromFavorites($postId);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список постов</title>
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
        .add-to-favorites {
            background-color: #ffd700; /* желтый */
            color: #000;
        }
        .remove-from-favorites {
            background-color: #4169E1; /* синий */
            color: #000000;
        }
        td button {
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
        }
        .last-comment {
            background-color: #71706d; /* серый */
            color: #000;
        }
        .modal {
            display: none; /* Скрываем модальное окно по умолчанию */
            position: fixed; /* Фиксированная позиция */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.5); /* Полупрозрачный фон */
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #888;
            text-align: center;
        }
    </style>
</head>
<body>
<h1>Список постов</h1>
<p style="text-align: right;"><a href="logout.php">Выход</a></p>
<p style="text-align: right;"><a href="favorites.php">Просмотреть избранное</a></p>
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Заголовок</th>
        <th>Текст</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($posts as $post):?>
        <tr>
            <td><?php echo $post['id']; ?></td>
            <td><?php echo $post['title']; ?></td>
            <td><?php echo $post['content']; ?></td>
            <td>
                <?php if (isFavorite($post['id'])): ?>
                    <form action="" method="post">
                        <button class="remove-from-favorites" type="submit" name="remove_from_favorites" value="<?php echo $post['id']; ?>">Удалить из избранного</button>
                    </form>
                <?php else: ?>
                    <form action="" method="post">
                        <button class="add-to-favorites" type="submit" name="add_to_favorites" value="<?php echo $post['id']; ?>">Добавить в избранное</button>
                    </form>
                <?php endif; ?>

                <button class="last-comment" type="button" name="last_comment" value="<?php echo $post['id']; ?>">Последний комментарий</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="modal" id="myModal">
    <div class="modal-content">
        <span onclick="closeModal()" style="float:right; cursor:pointer;">&times;</span>
        <p id="modalContent">Загрузка последнего комментария...</p>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.last-comment');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.value;
                fetch(`https://jsonplaceholder.typicode.com/posts/${postId}/comments`)
                    .then(response => response.json())
                    .then(comments => {
                        const lastComment = comments[comments.length - 1];
                        const modalContent = document.getElementById('modalContent');
                        modalContent.innerText = lastComment.body;
                        openModal();
                    });
            });
        });
    });

    function openModal() {
        document.getElementById('myModal').style.display = "block"; // Показываем модальное окно
    }

    function closeModal() {
        document.getElementById('myModal').style.display = "none"; // Скрываем модальное окно
    }
</script>
</body>
</html>