<?php
session_start();

// Oturum zaman aşımı kontrolü
$timeout_duration = 10 * 60; // 10 dakika

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Oturum süresi dolmuş, oturumu sonlandır
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=true');
    exit();
}

// Kalan süreyi hesapla
$remaining_time = $timeout_duration - (time() - $_SESSION['last_activity']);
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
    $action = $_POST['action'];
    $userId = $conn->real_escape_string($_POST['user_id']);

    if ($action == 'delete') {
        $sql = "DELETE FROM users WHERE id=$userId";
        if ($conn->query($sql) === TRUE) {
            $message = "Kullanıcı silindi!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }

    if ($action == 'update') {
        $username = $conn->real_escape_string($_POST['username']);
        $role = $conn->real_escape_string($_POST['role']);
        $new_password = !empty($_POST['new_password']) ? $conn->real_escape_string($_POST['new_password']) : null;

        if ($new_password) {
            $sql = "UPDATE users SET username='$username', role='$role', password='$new_password' WHERE id=$userId";
        } else {
            $sql = "UPDATE users SET username='$username', role='$role' WHERE id=$userId";
        }

        if ($conn->query($sql) === TRUE) {
            $message = "Kullanıcı güncellendi!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .user {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fafafa;
        }

        .user input[type="text"],
        .user input[type="password"],
        .user select {
            width: calc(20% - 10px);
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .user button {
            padding: 10px 15px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .user button:hover {
            background-color: #4cae4c;
        }

        .user .delete-button {
            background-color: #d9534f;
        }

        .user .delete-button:hover {
            background-color: #c9302c;
        }

        .user form {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .error {
            text-align: center;
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .nav-buttons a {
            display: block;
            width: 200px;
            padding: 10px;
            margin: 5px;
            background-color: #5cb85c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .nav-buttons a:hover {
            background-color: #4cae4c;
            transform: scale(1.05);
        }

        .back-button {
            background-color: #d9534f;
        }

        .back-button:hover {
            background-color: #c9302c;
        }

        /* Oturum süresi konteynır stili */
        #timer-container {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 5px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="timer-container">
        Oturum süresi: <span id="time"></span>
    </div>

    <div class="container">
        <h1>Kullanıcı Yönetimi</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="user">
                    <?php $current_password = $row['password']; ?>
                    <form class="user-form" method="POST" action="user_management.php">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        Kullanıcı Adı: <input type="text" name="username" value="<?php echo $row['username']; ?>" required>
                        Rol: 
                        <select name="role" required>
                            <option value="admin" <?php echo ($row['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="editor" <?php echo ($row['role'] == 'editor') ? 'selected' : ''; ?>>Editor</option>
                            <option value="user" <?php echo ($row['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        </select>
                        Şifre: <input type="text" name="current_password" value="<?php echo $current_password; ?>" readonly>
                        Yeni Şifre: <input type="password" name="new_password" placeholder="Yeni Şifre (Opsiyonel)">
                        <button type="submit" name="action" value="update">Güncelle</button>
                        <button type="submit" name="action" value="delete" class="delete-button">Sil</button>
                    </form>
                </div>
                <?php
            }
        } else {
            echo "Kullanıcı bulunamadı.";
        }
        ?>
        <div class="nav-buttons">
            <a href="dashboard.php">Ana Sayfa</a>
            <a href="futbolcuekle.php">Futbolcu Ekle</a>
            <a href="futbolcuduzenle.php">Futbolcuları Düzenle</a>
            <a href="futbolcuara.php">Futbolcu Ara</a>
            <a href="futbolcu.php">Futbolcuları Görüntüle</a>
            <a href="user_search.php">Kullanıcı Ara</a>
            <a href="user_management.php">Kullanıcı Yönetimi</a>
            <a href="javascript:history.back()" class="back-button">Geri Dön</a>
        </div>
    </div>

    <script>
        // Kalan süreyi dinamik olarak güncellemek için
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    window.location.href = "login.php?timeout=true";
                }
            }, 1000);
        }

        window.onload = function () {
            var remainingTime = <?php echo $remaining_time; ?>,
                display = document.querySelector('#time');
            startTimer(remainingTime, display);
        };
    </script>
</body>
</html>

<?php
$conn->close();
?>
