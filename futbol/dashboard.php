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

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        /* Temel stil ayarları */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            padding-top: 20px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            animation: fadeIn 2s;
        }

        p {
            font-size: 25px;
            line-height: 1.0;
            color: #c9302c;
            animation: slideIn 1.5s;
        }

        a {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #5cb85c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.3s;
            animation: bounceIn 2s;
        }

        a:hover {
            background-color: #4cae4c;
            transform: scale(1.1);
        }

        /* Admin özel stil */
        .admin-link {
            background-color: #d9534f;
        }

        .admin-link:hover {
            background-color: #c9302c;
            transform: scale(1.1);
        }

        /* Animasyonlar */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes bounceIn {
            from, 20%, 40%, 60%, 80%, to {
                animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
            }
            0% {
                opacity: 0;
                transform: translate3d(0, -3000px, 0);
            }
            60% {
                opacity: 1;
                transform: translate3d(0, 25px, 0);
            }
            80% {
                transform: translate3d(0, -10px, 0);
            }
            to {
                transform: translate3d(0, 0, 0);
            }
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
        <h1>ANA SAYFA</h1>
        <p>Hoş geldiniz: <?php echo $_SESSION['username']; ?></p>
        <p>Rolünüz: <?php echo $_SESSION['user_role']; ?></p>

        <?php if ($_SESSION['user_role'] == 'user'): ?>
            <a href="futbolcuekle.php">Futbolcu Ekle</a><br>
            <a href="futbolcu.php">Futbolcuları Görüntüle</a><br>
            <a href="futbolcuara.php">Futbolcu Ara</a><br>
            <a href="logout.php">Çıkış Yap</a><br>
        <?php elseif ($_SESSION['user_role'] == 'editor'): ?>
            <a href="futbolcuekle.php">Futbolcu Ekle</a><br>
            <a href="futbolcu.php">Futbolcuları Görüntüle</a><br>
            <a href="futbolcudüzenle.php">Futbolcuları Düzenle</a><br>
            <a href="futbolcuara.php">Futbolcu Ara</a><br>
            <a href="logout.php">Çıkış Yap</a><br>
        <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
            <a href="user_management.php" class="admin-link">Kullanıcı Yönetimi</a><br>
            <a href="futbolcuekle.php">Futbolcu Ekle</a><br>
            <a href="futbolcu.php">Futbolcuları Görüntüle</a><br>
            <a href="futbolcudüzenle.php">Futbolcuları Düzenle</a><br>
            <a href="futbolcuara.php">Futbolcu Ara</a><br>
            <a href="logout.php">Çıkış Yap</a><br>
        <?php endif; ?>
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
