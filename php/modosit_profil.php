<?php
session_start();
if (!isset($_SESSION['felhasznalo_nev'])) {
    header("Location: login.php");
    exit();
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
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">R&J</a></li>
                <li><a href="kapcsolat.php">Kapcsolat</a></li>
                <li><a href="jarmuvek.php">Bérlés</a></li>
                <li><a href="forum.php">Gépjárművek</a></li>
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

    <?php if (isset($_SESSION['alert_message'])): ?>
        <?php include 'modal.php'; ?>
    <?php endif; ?>

    <div class="container">
        <h2>Profil módosítása</h2>
        <form id="profileForm">
            <label for="nev">Név:</label>
            <input type="text" id="nev" name="nev" required>

            <label for="emailcim">Email cím:</label>
            <input type="email" id="emailcim" name="emailcim" required>

            <label for="szamlazasi_cim">Számlázási cím:</label>
            <input type="text" id="szamlazasi_cim" name="szamlazasi_cim" required>

            <label for="jogositvany_kiallitasDatum">Jogosítvány kiállítás dátuma:</label>
            <input type="date" id="jogositvany_kiallitasDatum" name="jogositvany_kiallitasDatum" required>

            <h3>Jelszó módosítása</h3>
            <label for="uj_jelszo">Új jelszó:</label>
            <input type="password" id="uj_jelszo" name="uj_jelszo">

            <label for="uj_jelszo_megerosites">Új jelszó megerősítése:</label>
            <input type="password" id="uj_jelszo_megerosites" name="uj_jelszo_megerosites">

            <input type="submit" value="Frissítés">
            <a href="profilom.php"><button class="back-btn" type="button">Vissza a profilomhoz</button></a>
        </form>
    </div>
    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("profileForm");

            // Fetch user data
            fetch("api/user.php")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("nev").value = data.nev;
                    document.getElementById("emailcim").value = data.emailcim;
                    document.getElementById("szamlazasi_cim").value = data.szamlazasi_cim;
                    document.getElementById("jogositvany_kiallitasDatum").value = data.jogositvany_kiallitasDatum;
                });

            // Submit form
            form.addEventListener("submit", function (e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch("api/user.php", {
                    method: "POST",
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Set success alert in session and reload
                            fetch("modal.php", {
                                method: "POST",
                                body: JSON.stringify({
                                    message: "Profil sikeresen frissítve!",
                                    type: "success"
                                }),
                                headers: {
                                    "Content-Type": "application/json"
                                }
                            }).then(() => window.location.reload());
                        } else {
                            // Set error alert in session and reload
                            fetch("modal.php", {
                                method: "POST",
                                body: JSON.stringify({
                                    message: data.message || "Hiba történt a frissítés során.",
                                    type: "warning"
                                }),
                                headers: {
                                    "Content-Type": "application/json"
                                }
                            }).then(() => window.location.reload());
                        }
                    });
            });
        });
    </script>
</body>

</html>