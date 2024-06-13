<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) { // Düz metin karşılaştırması
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time(); // Oturum başlangıç zamanını ayarla
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Yanlış şifre.";
        }
    } else {
        $error = "Kullanıcı bulunamadı.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        form {
            width: 300px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .error {
            color: #d9534f;
            text-align: center;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #5cb85c;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form id="login-form" method="POST" action="">
        <div class="error" id="error-message">
            <?php 
            if (isset($_GET['timeout']) && $_GET['timeout'] == 'true') {
                echo "Oturum süreniz doldu. Lütfen yeniden giriş yapın.";
            }
            if (isset($error)) {
                echo $error;
            }
            ?>
        </div>
        Kullanıcı Adı: <input type="text" name="username" required><br>
        Şifre: <input type="password" name="password" required><br>
        <button type="submit">Giriş Yap</button>
        <div class="register-link">
            Hesabınız yok mu? <a href="register.php">Kayıt Ol</a>
        </div>
    </form>
</body>
</html>
