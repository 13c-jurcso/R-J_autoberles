<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli("localhost", "root", "", "autoberles");

    if ($conn->connect_error) {
        die("Csatlakozási hiba: " . $conn->connect_error);
    }

    $jarmu_id = $_POST['jarmu_id'];
    $message = $_POST['message'];
    $userName = isset($_SESSION['felhasznalo_nev']) ? $_SESSION['felhasznalo_nev'] : 'Anonim';

    $query = "INSERT INTO velemenyek (jarmu_id, felhasznalo_nev, uzenet, datum) 
              VALUES ('$jarmu_id', '$userName', '$message', NOW())";

    if ($conn->query($query) === TRUE) {
        header("Location: auto_adatok.php?id=$jarmu_id");
    } else {
        echo "Hiba a vélemény mentésekor: " . $conn->error;
    }

    $conn->close();
}
?>
