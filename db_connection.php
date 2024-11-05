<?php
// db_connection.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autoberles"; // Az adatbázis neve

// Kapcsolódás az adatbázishoz
$db = new mysqli($servername, $username, $password, $dbname);

// Ellenőrzés, hogy sikerült-e kapcsolódni
if ($db->connect_errno) {
    die("Kapcsolódási hiba: " . $db->connect_error);
}
?>
