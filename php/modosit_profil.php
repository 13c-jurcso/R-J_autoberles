<?php
session_start();
include './db_connection.php';

// Modal include
if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}

// Ha a felhasználó nincs bejelentkezve, irányítsuk át a bejelentkező oldalra
if (!isset($_SESSION['felhasznalo_nev'])) {
    header("Location: login.php");
    exit();
}

// A bejelentkezett felhasználó neve
$felhasznalo_nev = $_SESSION['felhasznalo_nev'];

// SQL lekérdezés a felhasználó adatainak lekérdezésére
$sql = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = '$felhasznalo_nev'";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<p>Nincs ilyen felhasználó!</p>";
    exit();
}

// Ha az űrlap be van küldve, frissítsük az adatokat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];

    $update_sql = "UPDATE felhasznalo SET nev='$nev', emailcim='$emailcim', szamlazasi_cim='$szamlazasi_cim', jogositvany_kiallitasDatum='$jogositvany_kiallitasDatum' WHERE felhasznalo_nev='$felhasznalo_nev'";

    if ($db->query($update_sql) === TRUE) {
        $_SESSION['alert_message'] = "Az adatok sikeresen frissítve!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert_message'] = "Hiba történt az adatok frissítésekor: " . $db->error;
        $_SESSION['alert_type'] = "warning";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil módosítása</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007BFF;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="email"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .back-btn {
            text-align: center;
            align-items: center;
            appearance: none;
            background-color: #aceeb8;
            border-radius: 4px;
            border-width: 0;
            box-shadow: rgba(45, 35, 66, 0.4) 0 2px 4px,rgba(45, 35, 66, 0.3) 0 7px 13px -3px,#1b8a30 0 -3px 0 inset;
            box-sizing: border-box;
            color: #36395A;
            cursor: pointer;
            display: inline-flex;
            height: 48px;
            justify-content: center;
            line-height: 1;
            list-style: none;
            overflow: hidden;
            padding-left: 16px;
            padding-right: 16px;
            position: relative;
            text-align: left;
            text-decoration: none;
            transition: box-shadow .15s,transform .15s;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            white-space: nowrap;
            will-change: box-shadow,transform;
            font-size: 18px;
        }

        .back-btn:focus {
            box-shadow: #1b8a30 0 0 0 1.5px inset, rgba(45, 35, 66, 0.4) 0 2px 4px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #1b8a30 0 -3px 0 inset;
        }

        .back-btn:hover {
            box-shadow: rgba(45, 35, 66, 0.4) 0 4px 8px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #1b8a30 0 -3px 0 inset;
            transform: translateY(-2px);
        }

        .back-btn:active {
            box-shadow: #1b8a30 0 3px 7px inset;
            transform: translateY(2px);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Profil módosítása</h2>
    <form method="post" action="modosit_profil.php">
        <label for="nev">Név:</label>
        <input type="text" id="nev" name="nev" value="<?php echo htmlspecialchars($user['nev']); ?>" required>

        <label for="emailcim">Email cím:</label>
        <input type="email" id="emailcim" name="emailcim" value="<?php echo htmlspecialchars($user['emailcim']); ?>" required>

        <label for="szamlazasi_cim">Számlázási cím:</label>
        <input type="text" id="szamlazasi_cim" name="szamlazasi_cim" value="<?php echo htmlspecialchars($user['szamlazasi_cim']); ?>" required>

        <label for="jogositvany_kiallitasDatum">Jogosítvány kiállítás dátuma:</label>
        <input type="date" id="jogositvany_kiallitasDatum" name="jogositvany_kiallitasDatum" value="<?php echo htmlspecialchars($user['jogositvany_kiallitasDatum']); ?>" required>

        <input type="submit" value="Frissítés">

        <a href="profilom.php"><button class="back-btn">Vissza a profilomhoz</button></a>
    </form>
    
</div>

</body>
</html>