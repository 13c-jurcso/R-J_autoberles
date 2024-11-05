<?php
session_start();
include 'db_connection.php'; // Az adatbázis kapcsolatot be kell importálni

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];
    $jelszo = $_POST['jelszo'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jelszo_ujra = $_POST['jelszo_ujra'];

    // Ellenőrizzük, hogy a két jelszó megegyezik-e
    if ($jelszo !== $jelszo_ujra) {
        echo "A két jelszó nem egyezik!";
        exit();
    }

    // 1. Lépés: Ellenőrizzük, hogy létezik-e már a felhasználóneve
    $muvelet = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = '$felhasznalo_nev'";
    $adatok = adatokLekerese($muvelet);

    if (is_array($adatok) && count($adatok) > 0) {
        // Ha van találat, akkor már létezik a felhasználó
        echo "Ez a felhasználónév már létezik!";
    } else {
        // 2. Lépés: Ha nincs találat, akkor regisztrálhatjuk az új felhasználót

        // A jelszó titkosítása
        $hashed_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        // Felhasználó hozzáadása az adatbázishoz
        $insert_muvelet = "INSERT INTO felhasznalo (felhasznalo_nev, nev, emailcim,jogositvany_kiallitasDatum, szamlazasi_cim, jelszo) VALUES ('$felhasznalo_nev', '$nev', '$emailcim', '$jogositvany_kiallitasDatum', '$szamlazasi_cim', 0, '$jelszo')";
        if ($db->query($insert_muvelet)) {
            echo "Sikeres regisztráció!";
            // Átirányítás a bejelentkezéshez
            header("Location: login.php");
            exit();
        } else {
            echo "Hiba történt a regisztráció során: " . $db->error;
        }
    }
}

// Adatok lekérésére szolgáló függvény
function adatokLekerese($muvelet) {
    global $db; // Az adatbázis kapcsolatot használjuk

    $eredmeny = $db->query($muvelet);

    if ($db->errno == 0) {
        if ($eredmeny->num_rows > 0) {
            return $eredmeny->fetch_all(MYSQLI_ASSOC);
        } else {
            return []; // Ha nincs találat, egy üres tömböt adunk vissza
        }
    } else {
        return $db->error; // Hibát adunk vissza
    }
}
?>





<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Regisztráció</h2>
    <form action="register.php" method="post">
        <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
        <input type="text" name="nev" placeholder="Teljes név" required><br>
        <input type="email" name="emailcim" placeholder="Email cím" required><br>
        <input type="date" name="jogositvany_kiallitasDatum" placeholder="Jogosítvány kiállítás dátuma" required><br>
        <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required><br>
        <input type="password" name="jelszo" placeholder="Jelszó" required><br>
        <label for="jelszo_ujra">Jelszó újra</label>
        <input type="password" name="jelszo_ujra" id="jelszo_ujra" required><br>
        <button type="submit">Regisztráció</button>
        <a href="index.php">Vissza a főoldalra</a>
    </form>
</body>
</html>
