<?php
session_start();

// Modal include
if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>R&J Autókölcsönző</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script defer src="../index.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
    <h1>R&J autókölcsönző. Indulás!</h1>
    <div id="kezdo_input">
        <form action="jarmuvek.php" method="get">
            <select name="hely" id="hely">
                <option value="Veszprém">Veszprém</option>
                <option value="Budapest">Budapest</option>
                <option value="Debrecen">Debrecen</option>
            </select>
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
        <span id="closeLoginModal" class="close" onclick="closeModal()">&times;</span>
        <h2>Bejelentkezés</h2>
        <form action="login.php" method="post">
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
            <input type="password" name="jelszo" placeholder="Jelszó" required><br>
            <input type="submit" value="Bejelentkezés">
        </form>
    </div>
</div>

<div id="registerModal" class="modal">
    <div class="modal-content-register">
        <span id="closeRegisterModal" class="close" onclick="closeModal()">&times;</span>
        <h2>Regisztráció</h2>
        <form action="register.php" method="post">
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
            <input type="text" name="nev" placeholder="Teljes név" required><br>
            <input type="email" name="emailcim" placeholder="Email" required><br>
            <input type="password" name="jelszo" placeholder="Jelszó" required><br>
            <input type="password" name="jelszo_ujra" placeholder="Jelszó újra" required><br>
            <input type="date" name="jogositvany_kiallitasDatum" placeholder="Jogosítvány érvénnysségi dátuma" required><br>
            <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required><br>
            <input type="submit" value="Regisztráció">
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>