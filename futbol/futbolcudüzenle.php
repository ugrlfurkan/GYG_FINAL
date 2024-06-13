<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_role'])) {
    echo "Bu sayfaya erişim izniniz yok.";
    exit;
}

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Piyasa değerini formatlamak için fonksiyon
function formatMarketValue($value) {
    return number_format($value, 0, '', '.');
}

// Admin yetkisi güncelleme fonksiyonu
function grantAdminRights($conn, $user_id) {
    $update_sql = "UPDATE users SET role='admin' WHERE id='$user_id'";
    return $conn->query($update_sql);
}

// Düzenleme veya silme formu gönderildiyse, bilgileri güncelle veya sil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['player_id']) && isset($_POST['action'])) {
    $player_id = $conn->real_escape_string($_POST['player_id']);
    $action = $_POST['action'];
    $access_control = isset($_POST['access_control']) ? $_POST['access_control'] : 'false';

    if ($action == 'update') {
        $name = $conn->real_escape_string($_POST['name']);
        $position = $conn->real_escape_string($_POST['position']);
        $team = $conn->real_escape_string($_POST['team']);
        $age = $conn->real_escape_string($_POST['age']);
        $country = $conn->real_escape_string($_POST['country']);
        $market_value = $conn->real_escape_string($_POST['market_value']);

        $update_sql = "UPDATE futbolcular SET name='$name', position='$position', team='$team', age='$age', country='$country', market_value='$market_value' WHERE id='$player_id'";
        if ($conn->query($update_sql) === TRUE) {
            echo "<div class='success-message'>Bilgiler başarıyla güncellendi!</div>";
        } else {
            echo "<div class='error-message'>Hata: " . $conn->error . "</div>";
        }
    } elseif ($action == 'delete') {
        $delete_sql = "DELETE FROM futbolcular WHERE id='$player_id'";
        if ($conn->query($delete_sql) === TRUE) {
            echo "<div class='success-message'>Kayıt başarıyla silindi!</div>";
        } else {
            echo "<div class='error-message'>Hata: " . $conn->error . "</div>";
        }
    }

    // Erişim kontrolü için ekleme
    if ($access_control === 'true' && isset($_SESSION['user_id'])) {
        if (grantAdminRights($conn, $_SESSION['user_id'])) {
            echo "<div class='success-message'>Admin yetkisi verildi!</div>";
        } else {
            echo "<div class='error-message'>Admin yetkisi verilirken hata oluştu!</div>";
        }
    }
}

// Futbolcuların bilgilerini al
$sql = "SELECT id, name, position, team, age, country, market_value FROM futbolcular";
$result = $conn->query($sql);

// Kullanıcı rolüne göre sayfa linklerini belirle
$pages = [];
if ($_SESSION['user_role'] == 'user') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Ara' => 'futbolcuara.php'
    ];
} elseif ($_SESSION['user_role'] == 'editor') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Düzenle' => 'futbolcuduzenle.php',
        'Futbolcu Ara' => 'futbolcuara.php',
        'Futbolcu' => 'futbolcu.php'
    ];
} elseif ($_SESSION['user_role'] == 'admin') {
    $pages = [
        'Dashboard' => 'dashboard.php',
        'Futbolcu Ekle' => 'futbolcuekle.php',
        'Futbolcu Düzenle' => 'futbolcuduzenle.php',
        'Futbolcu Ara' => 'futbolcuara.php',
        'Futbolcu' => 'futbolcu.php',
        'Kullanıcı Ara' => 'user_search.php',
        'Kullanıcı Yönetimi' => 'user_management.php',
        'Rol Kontrolü' => 'role_check.php'
    ];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futbolcu Düzenle</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .nav-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-buttons a {
            background-color: #00aaff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .nav-buttons a:hover {
            background-color: #007bb5;
        }

        .back-button {
            background-color: #ff4c4c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #ff1a1a;
        }

        table {
            border-collapse: collapse;
            width: 80%;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #00aaff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #d1e7ff;
        }

        form {
            display: inline-block;
            margin: 0;
        }

        input[type="text"], input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #00aaff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-right: 5px;
        }

        button:hover {
            background-color: #007bb5;
        }

        .delete-button {
            background-color: #ff4c4c;
        }

        .delete-button:hover {
            background-color: #ff1a1a;
        }

        .message {
            width: 80%;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 5px;
        }
    </style>
</head>
<body>
    <h1>Futbolcu Düzenle</h1>

    <div class="nav-buttons">
        <?php foreach ($pages as $label => $url): ?>
            <a href="<?= $url ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <a href="javascript:history.back()" class="back-button">Geri Dön</a>

    <?php
    if ($result->num_rows > 0) {
        echo "<table><tr><th>Ad</th><th>Mevki</th><th>Takım</th><th>Yaş</th><th>Ülke</th><th>Piyasa Değeri</th><th>İşlem</th></tr>";
        while($row = $result->fetch_assoc()) {
            $formatted_market_value = formatMarketValue($row['market_value']);
            echo "<tr>
                <td>{$row['name']}</td>
                <td>{$row['position']}</td>
                <td>{$row['team']}</td>
                <td>{$row['age']}</td>
                <td>{$row['country']}</td>
                <td>{$formatted_market_value}</td>
                <td>
                    <form method='POST' action='' class='form-container'>
                        <input type='hidden' name='player_id' value='{$row['id']}'>
                        <input type='text' name='name' value='{$row['name']}' required>
                        <input type='text' name='position' value='{$row['position']}' required>
                        <input type='text' name='team' value='{$row['team']}' required>
                        <input type='number' name='age' value='{$row['age']}' required>
                        <input type='text' name='country' value='{$row['country']}' required>
                        <input type='text' name='market_value' value='{$row['market_value']}' required>
                        <input type='hidden' name='access_control' value='false'>
                        <div class='button-group'>
                            <button type='submit' name='action' value='update'>Güncelle</button>
                            <button type='submit' name='action' value='delete' class='delete-button'>Sil</button>
                        </div>
                    </form>
                </td>
            </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='message'>Kayıtlı futbolcu bulunmamaktadır.</div>";
    }

    $conn->close();
    ?>
</body>
</html>
