<?php
$servername = "localhost";
$username = "root"; // Ha más adatbázis felhasználód van, akkor ezt módosítsd
$password = ""; // Ha van jelszavad, írd be
$dbname = "autoberles"; // Az adatbázis neve

// Kapcsolódás létrehozása
$conn = new mysqli($servername, $username, $password, $dbname);

// Kapcsolat ellenőrzése
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$db->set_charset("utf8mb4");
?>
