<?php
session_start();

if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J Autókölcsönző</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="menu-toggle">☰ Menu</div>
    <nav>
        <ul>
            <li><a href="index.php">R&J</a></li>
            <li><a href="kapcsolat.php">Kapcsolat</a></li>
            <li><a href="forum.php">Fórum</a></li>
            <li><a href="husegpontok.php">Hűségpontok</a></li>
            <li><a href="jarmuvek.php">Gépjárművek</a></li>
            <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                <li><a href="profilom.php">Profilom</a></li>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="#" onclick="openModal('loginModal')">Bejelentkezés</a></li>
                <li><a href="#" onclick="openModal('registerModal')">Regisztráció</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div id="torzs">
    <h1 class="cim">R&J autókölcsönző. Indulás!</h1>
    <div id="kezdo_input">
        <form action="jarmuvek.php" method="get">
            <label id="datuma">Átvétel dátuma</label>
            <input type="date" name="atvetel" required>
            <label id="datuma">Leadás dátuma</label>
            <input type="date" name="leadas" required>
            <input type="submit" value="Járművek megtekintése">
        </form>
    </div>
</div>

<div id="loginModal" class="modal">
    <div class="modal-content-login">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Bejelentkezés</h2>
        <form action="login.php" method="post">
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required>
            <input type="password" name="jelszo" placeholder="Jelszó" required>
            <input type="submit" value="Bejelentkezés">
        </form>
    </div>
</div>

<div id="registerModal" class="modal">
    <div class="modal-content-register">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Regisztráció</h2>
        <form action="register.php" method="post">
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required>
            <input type="text" name="nev" placeholder="Teljes név" required>
            <input type="email" name="emailcim" placeholder="Email" required>
            <input type="password" name="jelszo" placeholder="Jelszó" required>
            <input type="password" name="jelszo_ujra" placeholder="Jelszó újra" required>
            <input type="date" name="jogositvany_kiallitasDatum" placeholder="Jogosítvány érvényességi dátuma" required>
            <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required>
            <input type="submit" value="Regisztráció">
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../index.js"></script>
</body>
</html>