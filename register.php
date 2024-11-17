<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];
    $jelszo = $_POST['jelszo'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jelszo_ujra = $_POST['jelszo_ujra'];

    if ($jelszo !== $jelszo_ujra) {
        echo "A két jelszó nem egyezik!";
        exit();
    }

    $query = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $felhasznalo_nev);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Ez a felhasználónév már létezik!";
    } else {
        $hashed_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO felhasznalo (felhasznalo_nev, nev, emailcim, jogositvany_kiallitasDatum, szamlazasi_cim, jelszo) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->bind_param("ssssss", $felhasznalo_nev, $nev, $emailcim, $jogositvany_kiallitasDatum, $szamlazasi_cim, $hashed_jelszo);
        if ($stmt->execute()) {
            echo "Sikeres regisztráció!";
            header("Location: login.php");
            exit();
        } else {
            echo "Hiba történt a regisztráció során: " . $stmt->error;
        }
    }
}
?>
