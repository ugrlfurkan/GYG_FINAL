<?php
session_start();

// Kullanıcı girişi yapılmış mı kontrol edelim
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $position = $conn->real_escape_string($_POST['position']);
    $team = $conn->real_escape_string($_POST['team']);
    $age = (int)$_POST['age'];
    $country = $conn->real_escape_string($_POST['country']);
    $market_value = (float)$_POST['market_value'];
    $added_by = $_SESSION['user_id'];

    $sql = "INSERT INTO futbolcular (name, position, team, age, country, market_value, added_by) 
            VALUES ('$name', '$position', '$team', '$age', '$country', '$market_value', '$added_by')";

    if ($conn->query($sql) === TRUE) {
        $message = "Futbolcu başarıyla eklendi!";
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futbolcu Ekle</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        .message, .error {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            color: #fff;
            text-align: center;
        }

        .message {
            background-color: #4CAF50;
        }

        .error {
            background-color: #f44336;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type=text],
        input[type=number] {
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
            margin-top: 10px;
        }

        button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Futbolcu Ekle</h1>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form id="add-player-form" method="POST" action="">
            <input type="text" name="name" placeholder="Futbolcu Adı" required>
            <input type="text" name="position" placeholder="Pozisyon" required>
            <input type="text" name="team" placeholder="Takım" required>
            <input type="number" name="age" placeholder="Yaş" required>
            <input type="text" name="country" placeholder="Ülke" required>
            <input type="number" step="0.01" name="market_value" placeholder="Piyasa Değeri (Milyon €)" required>
            <button type="submit">Ekle</button>
        </form>
    </div>
</body>
</html>
