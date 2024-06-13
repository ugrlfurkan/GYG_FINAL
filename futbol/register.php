<?php
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

    if ($conn->connect_error) {
        die("Bağlantı hatası: " . $conn->connect_error);
    }

    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);  // Şifre şifrelenmeden saklanacak
    $role = 'user';  // Varsayılan rol

    // Kullanıcı adı zaten var mı kontrol et
    $check_sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $error_message = "Bu kullanıcı adı zaten kullanılıyor.";
    } else {
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Kayıt başarılı!";
        } else {
            $error_message = "Hata: " . $sql . '<br>' . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Kaydı</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
        }

        .message {
            width: 100%;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        form {
            background: white;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        form div {
            margin-bottom: 15px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if ($success_message): ?>
        <div class="message success-message"><?= $success_message ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <form id="register-form" method="POST" action="">
        <div>
            Kullanıcı Adı: <input type="text" name="username" required>
        </div>
        <div>
            Şifre: <input type="password" name="password" required>
        </div>
        <div>
            <button type="submit">Kaydol</button>
        </div>
        <div class="login-link">
            Zaten bir hesabınız var mı? <a href="login.php">Giriş Yap</a>
        </div>
    </form>
</body>
</html>
