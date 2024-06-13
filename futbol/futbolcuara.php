<?php
session_start();

// Oturum süresi 10 dakika olarak ayarlanıyor
$session_duration = 10 * 60; // 10 dakika saniye cinsinden

// Eğer kullanıcı oturum süresini geçmişse veya oturum başlatılmamışsa, kullanıcıyı login sayfasına yönlendir
if (!isset($_SESSION['user_id']) || (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $session_duration)) {
    session_unset();
    session_destroy();
    header('Location: login.php?session_expired=true');
    exit();
}

// Oturum süresini güncelle
$_SESSION['last_activity'] = time();

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_results = [];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_name = $_POST['search_name']; // Güvensiz kullanıcı girdisi

    // Güvensiz SQL sorgusu
    $sql = "SELECT * FROM futbolcular WHERE name LIKE '%$search_name%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
    } else {
        $message = "Bu isimde futbolcu bulunamadı: '$search_name'.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Futbolcular Arama Portalı</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        #session-timer {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #ffffff;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        #session-alert {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }
        #session-alert button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="session-timer">
        Oturum Süresi: <span id="session-time"></span>
    </div>

    <h1>Futbolcuları Arama Portalı</h1>

    <form id="search-form" method="POST" action="">
        Futbolcu Adı: <input type="text" name="search_name" required>
        <button type="submit">Ara</button>
    </form>

    <div id="search-results">
        <?php if (!empty($search_results)): ?>
            <h2>Arama Sonuçları:</h2>
            <ul>
                <?php foreach ($search_results as $futbolcu): ?>
                    <li>
                        <strong>Adı:</strong> <?= htmlspecialchars($futbolcu['name']); ?><br>
                        <strong>Pozisyon:</strong> <?= htmlspecialchars($futbolcu['position']); ?><br>
                        <strong>Takım:</strong> <?= htmlspecialchars($futbolcu['team']); ?><br>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php elseif (isset($message)): ?>
            <p><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <div id="session-alert">
        <p>Oturum süreniz dolmuştur!</p>
        <button id="continue-button">Devam Et</button>
        <span id="countdown"></span>
    </div>

    <script>
        var sessionDuration = <?php echo $session_duration; ?>;
        var sessionTimeElement = document.getElementById('session-time');
        var sessionAlert = document.getElementById('session-alert');

        // Oturum süresini başlat
        var sessionTimer = setInterval(function() {
            sessionDuration--;
            var minutes = Math.floor(sessionDuration / 60);
            var seconds = sessionDuration % 60;
            sessionTimeElement.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');

            if (sessionDuration <= 0) {
                clearInterval(sessionTimer);
                sessionAlert.style.display = 'block';
                startCountdown();
            }
        }, 1000);

        // Geri sayım başlat
        function startCountdown() {
            var countdown = 10; // 10 saniye
            var countdownElement = document.getElementById('countdown');
            countdownElement.textContent = countdown;

            var countdownInterval = setInterval(function() {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(countdownInterval);
                    // Oturum süresi dolmasına rağmen kullanıcı devam etmezse
                    window.location.href = 'login.php'; // Login sayfasına yönlendir
                }
            }, 1000);
        }

        // Devam et butonuna tıklanınca
        document.getElementById('continue-button').addEventListener('click', function() {
            sessionAlert.style.display = 'none';
            clearInterval(sessionTimer);
            location.reload(); // Sayfayı yeniden yükle
        });
    </script>
</body>
</html>
