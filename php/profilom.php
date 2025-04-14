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
    <title>R&J - Profilom</title>
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-card {
            max-width: 600px;
            margin: 0 auto;
        }

        .table-container {
            margin-top: 40px;
        }

        .admin-btn {
            display: none;
        }
    </style>
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
                    <li><a href="profilom.php" class="active">Profilom</a></li>
                    <li><a href="logout.php">Kijelentkezés</a></li>
                <?php else: ?>
                    <li><a href="#" onclick="openModal('loginModal')">Bejelentkezés</a></li>
                    <li><a href="#" onclick="openModal('registerModal')">Regisztráció</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container ">
        <div class="card p-4 mb-4 profile-card">
            <h2 class="text-center">Profilom</h2>
            <p><strong>Felhasználónév:</strong> <span id="felhasznalo_nev"></span></p>
            <p><strong>Név:</strong> <span id="nev"></span></p>
            <p><strong>Email:</strong> <span id="emailcim"></span></p>
            <p><strong>Számlázási cím:</strong> <span id="szamlazasi_cim"></span></p>
            <p><strong>Jogosítvány kiállítás dátuma:</strong> <span id="jogositvany_kiallitasDatum"></span></p>
            <p><strong>Hűségpontjaim:</strong> <span id="husegpontok"></span></p>

            <div class="mt-3 text-center">
                <a href="modosit_profil.php" class="btn btn-secondary">Profil módosítása</a>
                <a id="adminPanelButton" href="../admin/php/autok_kezeles.php" class="btn btn-secondary admin-btn" style="display: none;">Vezérlőpult</a>
            </div>
        </div>
    </div>

    <div class="container table-container">
        <h3 class="text-center mb-3">Korábbi bérléseim</h3>
        <div id="berlesek"></div>
    </div>

    <footer class="text-center mt-4">
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Fetch user profile
            fetch("api/user.php")
                .then(response => response.json())
                .then(data => {
                    document.getElementById("felhasznalo_nev").textContent = data.felhasznalo_nev;
                    document.getElementById("nev").textContent = data.nev;
                    document.getElementById("emailcim").textContent = data.emailcim;
                    document.getElementById("szamlazasi_cim").textContent = data.szamlazasi_cim || "Nincs megadva";
                    document.getElementById("jogositvany_kiallitasDatum").textContent = data.jogositvany_kiallitasDatum || "Nincs megadva";
                    document.getElementById("husegpontok").textContent = data.husegpontok || 0;

                    // Show admin panel button if the user is an admin
                    if (data.admin == 1) {
                        document.getElementById("adminPanelButton").style.display = "inline-block";
                    }
                });

            // Fetch rental data
            fetch("api/rentals.php")
                .then(response => response.json())
                .then(data => {
                    const berlesekDiv = document.getElementById("berlesek");
                    if (data.length > 0) {
                        let table = `<table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Gyártó</th>
                                    <th>Típus</th>
                                    <th>Motor</th>
                                    <th>Napi Ár (Ft)</th>
                                    <th>Bérlés kezdete</th>
                                    <th>Bérlés vége</th>
                                    <th>Teljes Összeg (Ft)</th>
                                    <th>Kifizetve</th>
                                </tr>
                            </thead>
                            <tbody>`;
                        data.forEach(berles => {
                            table += `<tr>
                                <td>${berles.gyarto}</td>
                                <td>${berles.tipus}</td>
                                <td>${berles.motor}</td>
                                <td>${berles.ar}</td>
                                <td>${berles.tol}</td>
                                <td>${berles.ig}</td>
                                <td>${berles.osszeg}</td>
                                <td>${berles.kifizetve ? "Igen" : "Nem"}</td>
                            </tr>`;
                        });
                        table += `</tbody></table>`;
                        berlesekDiv.innerHTML = table;
                    } else {
                        berlesekDiv.innerHTML = "<p class='text-center'>Nincsenek korábbi bérléseid.</p>";
                    }
                });
        });
    </script>
</body>

</html>