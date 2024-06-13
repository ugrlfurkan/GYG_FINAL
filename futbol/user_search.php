<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$searchResults = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $searchResults .= "<ul>";
        while ($row = $result->fetch_assoc()) {
            $searchResults .= "<li>ID: " . $row['id'] . " - Kullanıcı Adı: " . $row['username'] . "</li>";
        }
        $searchResults .= "</ul>";
    } else {
        $searchResults .= "Kullanıcı bulunamadı.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Ara</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        input[type=text] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background-color: #eee;
            margin: 5px 0;
            padding: 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <form id="search-form" method="POST" action="">
        Kullanıcı Adı: <input type="text" name="username" required><br>
        <button type="submit">Ara</button>
    </form>
    <div id="search-results">
        <?php
        if (!empty($searchResults)) {
            echo $searchResults;
        }
        ?>
    </div>
</body>
</html>
