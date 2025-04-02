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
    <title>R&J - Profil módosítása</title>
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>
</body>

</html>