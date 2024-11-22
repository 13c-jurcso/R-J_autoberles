<?php
session_start();
include 'db_connection.php';

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
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<!-- Navbar -->
<header>
    <div class="menu-toggle">☰ Menu</div>
    <nav>
        <ul>
            <li><a href="index.php">R&J</a></li>
            <li><a href="kapcsolat.php">Kapcsolat</a></li>
            <li><a href="husegpontok.php">Hűségpontok</a></li>
            <li><a href="jarmuvek.php">Gépjárművek</a></li>
            <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Profil oldal tartalma -->
<div class="container">
    <h2>Profilom</h2>
    <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['felhasznalo_nev']); ?></p>
    <p><strong>Név:</strong> <?php echo htmlspecialchars($user['nev']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['emailcim']); ?></p>
    <p><strong>Számlázási cím:</strong> <?php echo htmlspecialchars($user['szamlazasi_cim']); ?></p>
    <p><strong>Jogosítvány kiállítás dátuma:</strong> <?php echo htmlspecialchars($user['jogositvany_kiallitasDatum']); ?></p>
    <p><strong>Hűségpontjaim:</strong> <?php echo htmlspecialchars($user['husegpontok']); ?></p>

    <!-- Módosítás gomb -->
    <a href="modosit_profil.php"><button class="back-btn">Profil módosítása</button></a>
</div>

<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
</script>

</body>
</html>
