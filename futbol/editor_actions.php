<?php
require 'role_check.php';
checkRole('editor');

$conn = new mysqli('localhost', 'root', 'abc123', 'futbol');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['delete'])) {
    $futbolcuId = $_POST['futbolcu_id'];
    $sql = "DELETE FROM futbolcular WHERE id=$futbolcuId";
    if ($conn->query($sql) === TRUE) {
        echo "Futbolcu silindi!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$sql = "SELECT * FROM futbolcular";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Futbolcu: " . $row['name'] . ", Pozisyon: " . $row['position'] . ", Takım: " . $row['team'];
        echo "<form method='POST' action=''>";
        echo "<input type='hidden' name='futbolcu_id' value='" . $row['id'] . "'>";
        echo "<button type='submit' name='delete'>Sil</button>";
        echo "</form><br>";
    }
} else {
    echo "Futbolcu bulunamadı.";
}

$conn->close();
?>