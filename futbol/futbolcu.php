<?php
session_start();

// Oturum zaman aşımı kontrolü
$timeout_duration = 600; // 10 dakika

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Oturum süresi dolmuş, oturumu sonlandır
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=true');
    exit();
}

// Oturum süresini yenile
$_SESSION['last_activity'] = time();

// Kalan süreyi hesapla
$remaining_time = $timeout_duration - (time() - $_SESSION['last_activity']);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user role
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT role FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

$sql = "SELECT futbolcular.*, users.username 
        FROM futbolcular 
        JOIN users ON futbolcular.added_by = users.id";
$result = $conn->query($sql);

// Kullanıcı rolüne göre sayfa linklerini belirle
$pages = [];
if ($user['role'] == 'user') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Ara' => 'futbolcuara.php'
    ];
} elseif ($user['role'] == 'editor') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Düzenle' => 'futbolcudüzenle.php',
        'Futbolcu Ara' => 'futbolcuara.php',
        'Futbolcu' => 'futbolcu.php'
    ];
} elseif ($user['role'] == 'admin') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Düzenle' => 'futbolcudüzenle.php',
        'Futbolcu Ara' => 'futbolcuara.php',
        'Futbolcu' => 'futbolcu.php',
        'Kullanıcı Ara' => 'user_search.php',
        'Kullanıcı Yönetimi' => 'user_management.php'
    ];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futbolcular Listesi</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        /* Temel stil ayarları */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 2s;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            animation: slideIn 1.5s;
        }

        .futbolcu-listesi {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            list-style: none;
            padding: 0;
        }

        .futbolcu-listesi li {
            flex: 1 1 calc(33.333% - 40px);
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
        }

        .futbolcu-listesi li:hover {
            transform: scale(1.05);
            background-color: #e9f5ff;
        }

        .edit-button {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 10px;
        }

        .edit-button:hover {
            background-color: #0056b3;
            transform: scale(1.1);
        }

        .nav-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-buttons a {
            background-color: #00aaff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            animation: bounceIn 2s;
        }

        .nav-buttons a:hover {
            background-color: #007bb5;
            transform: scale(1.1);
        }

        .back-button {
            background-color: #ff4c4c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            margin-bottom: 20px;
            position: center;
        }

        .back-button:hover {
            background-color: #ff1a1a;
            transform: scale(1.1);
        }

        /* Oturum süresi stili */
        .remaining-time {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ffcc00;
            padding: 10px;
            border-radius: 5px;
            color: #333;
            font-weight: bold;
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
    </style>
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
</head>
<body>
    <div class="remaining-time">
        Oturum süresi: <span id="time"><?php echo gmdate("i:s", $remaining_time); ?></span>
    </div>
    <div class="container">
        <h2>Futbolcular Listesi</h2>
        <div class="nav-buttons">
            <?php foreach ($pages as $label => $url): ?>
                <a href="<?= $url ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
        <a href="javascript:history.back()" class="back-button">Geri Dön</a>
        <ul class="futbolcu-listesi">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <div>
                            <strong>Futbolcu:</strong> <?php echo $row['name']; ?><br>
                            <strong>Pozisyon:</strong> <?php echo $row['position']; ?><br>
                            <strong>Takım:</strong> <?php echo $row['team']; ?><br>
                            <strong>Yaş:</strong> <?php echo $row['age']; ?><br>
                            <strong>Ülke:</strong> <?php echo $row['country']; ?><br>
                            <strong>Piyasa Değeri:</strong> <?php echo number_format($row['market_value'], 2, ',', '.'); ?> Milyon €<br>
                            <strong>Ekleyen:</strong> <?php echo $row['username']; ?><br>
                        </div>
                        <?php if ($user['role'] === 'admin' || $user['role'] === 'editor'): ?>
                            <a href="futbolcudüzenle.php?id=<?php echo $row['id']; ?>" class="edit-button">Düzenle</a>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>Futbolcu bulunamadı.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>

<?php
$conn->close();
?>
