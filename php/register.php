<?php
session_start();
if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}
include './db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];
    $jelszo = $_POST['jelszo'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jelszo_ujra = $_POST['jelszo_ujra'];

    if ($jelszo !== $jelszo_ujra) {
        $_SESSION['alert_type'] = "A két jelszó nem egyezik!";
        $_SESSION['alert_type'] = "warning";
                header("Location: index.php");
                exit();
    }

    $query = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $felhasznalo_nev);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['alert_type'] = "Ez a felhasználónév már létezik!";
        $_SESSION['alert_type'] = "warning";
                header("Location: index.php");
                exit();
    } else {
        $hashed_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO felhasznalo (felhasznalo_nev, nev, emailcim, jogositvany_kiallitasDatum, szamlazasi_cim, jelszo) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->bind_param("ssssss", $felhasznalo_nev, $nev, $emailcim, $jogositvany_kiallitasDatum, $szamlazasi_cim, $hashed_jelszo);
        if ($stmt->execute()) {
            $_SESSION['alert_type'] = "Sikeres regisztráció!";
            $_SESSION['alert_type'] = "warning";
            header("Location: index.php");
            exit();
        } else {
           $_SESSION['alert_type'] = "Hiba történt a regisztráció során: " . $stmt->error;
            $_SESSION['alert_type'] = "warning";
                header("Location: index.php");
                exit();
        }
    }
}
?>
