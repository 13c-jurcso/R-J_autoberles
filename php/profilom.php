<?php
session_start();
include './db_connection.php';

// Modal include
// Helyezd el a modal.php include-ot oda, ahol a HTML-ben meg szeretnéd jeleníteni,
// általában a <body> végén vagy egy dedikált helyen.
// Itt csak az üzenet meglétét ellenőrizzük.
$showModal = isset($_SESSION['alert_message']);


// Ha a felhasználó nincs bejelentkezve, irányítsuk át a bejelentkező oldalra
if (!isset($_SESSION['felhasznalo_nev'])) {
    // Mielőtt átirányítanál, beállíthatsz egy üzenetet is, ha akarsz
    // $_SESSION['alert_message'] = "Kérjük, jelentkezzen be a profil megtekintéséhez.";
    // $_SESSION['alert_type'] = "warning";
    header("Location: index.php"); // Jobb az indexre irányítani, ahol a login modal van
    exit();
}

// A bejelentkezett felhasználó neve
$felhasznalo_nev = $_SESSION['felhasznalo_nev'];

// SQL lekérdezés a felhasználó adatainak lekérdezésére (Prepared Statement használata biztonságosabb!)
$sql = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
$stmt_user = $db->prepare($sql);
$stmt_user->bind_param("s", $felhasznalo_nev);
$stmt_user->execute();
$result = $stmt_user->get_result();


if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // Kevesebb eséllyel fordul elő, ha a session-ből jön a név, de jó lekezelni
    echo "<p>Hiba: A felhasználói adatok nem találhatók!</p>";
    // Ideálisabb lenne egy szebb hibaoldalra irányítás
    exit();
}
$stmt_user->close(); // Lezárjuk a statementet

// SQL lekérdezés a felhasználó bérléseinek lekérdezésére (Prepared Statement használata itt is!)
// Az eredeti számításod helyes, de a LEFT JOIN akciós árakat nem veszi figyelembe.
// Ha kell az akciós ár, bonyolultabb lekérdezés kell. Maradjunk az egyszerűnél most.
$berlesek_sql = "SELECT b.berles_id, b.tol, b.ig, b.kifizetve, j.gyarto, j.ar, j.tipus, j.motor,
                (DATEDIFF(b.ig, b.tol) + 1) * j.ar AS osszeg
                FROM berlesek AS b JOIN jarmuvek AS j ON b.jarmu_id = j.jarmu_id
                WHERE b.felhasznalo = ?
                ORDER BY b.tol DESC"; // Rendezhetjük dátum szerint
$stmt_berles = $db->prepare($berlesek_sql);
$stmt_berles->bind_param("s", $felhasznalo_nev);
$stmt_berles->execute();
$berlesek_result = $stmt_berles->get_result();

?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J - Profilom - <?php echo htmlspecialchars($user['nev']); ?></title> <!-- Dinamikus cím -->
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css//bootstrap.min.css">

    <!-- CSS stílus a jobbra igazításhoz -->
    <style>
        .currency {
            text-align: right;
        }

        /* Opcionális: a fejlécet is igazíthatjuk */
        th.currency {
            text-align: right;
        }

        /* Javíthatjuk a táblázat olvashatóságát */
        .tablazat table {
            margin-top: 20px;
            word-break: keep-all;
            /* Ne törje el a szavakat, ha lehet */
        }

        .tablazat th,
        .tablazat td {
            white-space: nowrap;
            /* Ne törje több sorba a cella tartalmát */
            vertical-align: middle;
            /* Vertikális középre igazítás */
        }
    </style>
</head>

<body>

    <header>
        <!-- Header változatlan -->
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">R&J</a></li>
                <li><a href="kapcsolat.php">Kapcsolat</a></li>
                <li><a href="husegpontok.php">Hűségpontok</a></li>
                <li><a href="jarmuvek.php">Gépjárművek</a></li>
                <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                    <li><a href="profilom.php" class="active">Profilom</a></li> <!-- Aktív menüpont jelölése -->
                    <li><a href="logout.php">Kijelentkezés</a></li>
                <?php else: ?>
                    <li><a href="#" onclick="openModal('loginModal')">Bejelentkezés</a></li>
                    <li><a href="#" onclick="openModal('registerModal')">Regisztráció</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="container mt-4"> <!-- Bootstrap margó -->
        <div class="card p-4 mb-4"> <!-- Bootstrap kártya a jobb kinézethez -->
            <h2>Profilom</h2>
            <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['felhasznalo_nev']); ?></p>
            <p><strong>Név:</strong> <?php echo htmlspecialchars($user['nev']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['emailcim']); ?></p>
            <p><strong>Számlázási cím:</strong> <?php echo htmlspecialchars($user['szamlazasi_cim'] ?? 'Nincs megadva'); ?></p> <!-- Null coalescing operator ?? -->
            <p><strong>Jogosítvány kiállítás dátuma:</strong> <?php echo htmlspecialchars($user['jogositvany_kiallitasDatum'] ?? 'Nincs megadva'); ?></p>
            <p><strong>Hűségpontjaim:</strong> <?php echo htmlspecialchars(number_format($user['husegpontok'] ?? 0, 0, ',', ' ')); ?></p> <!-- Formázás itt is -->

            <div class="mt-3"> <!-- Gombokhoz margó -->
                <?php if ($user['admin'] == 1): ?>
                    <a href="../admin/php/autok_kezeles.php" class="btn btn-secondary">Vezérlőpult</a>
                <?php endif; ?>
                <a href="modosit_profil.php" class="btn btn-secondary">Profil módosítása</a>
            </div>
        </div>
    </div>

    <div class="container tablazat"> <!-- Container itt is a jobb igazításhoz -->
        <h3 class="text-center mb-3">Korábbi bérléseim</h3> <!-- Bootstrap class-ok -->
        <?php if ($berlesek_result && $berlesek_result->num_rows > 0): ?>
            <div class="table-responsive"> <!-- Reszponzivitás kis képernyőn -->
                <table class="table table-striped table-hover"> <!-- Bootstrap class-ok -->
                    <thead class="table-light"> <!-- Fejléc kiemelése -->
                        <tr>
                            <th>Gyártó</th>
                            <th>Típus</th>
                            <th>Motor</th>
                            <!-- CSS Osztály hozzáadva a fejléchez -->
                            <th class="currency">Napi Ár (Ft)</th>
                            <th>Bérlés kezdete</th>
                            <th>Bérlés vége</th>
                            <!-- CSS Osztály hozzáadva a fejléchez -->
                            <th class="currency">Teljes Összeg (Ft)</th>
                            <th>Kifizetve</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($berles = $berlesek_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($berles['gyarto']); ?></td>
                                <td><?php echo htmlspecialchars($berles['tipus']); ?></td>
                                <td><?php echo htmlspecialchars($berles['motor']); ?></td>
                                <!-- CSS Osztály hozzáadva az adatcellához -->
                                <td class="currency"><?php echo htmlspecialchars(number_format($berles['ar'], 0, ',', ' ')); ?></td>
                                <td><?php echo htmlspecialchars($berles['tol']); ?></td>
                                <td><?php echo htmlspecialchars($berles['ig']); ?></td>
                                <!-- CSS Osztály hozzáadva az adatcellához -->
                                <td class="currency"><?php echo htmlspecialchars(number_format($berles['osszeg'], 0, ',', ' ')); ?></td>
                                <td><?php echo $berles['kifizetve'] ? '<span class="badge bg-success">Igen</span>' : '<span class="badge bg-warning text-dark">Nem</span>'; ?></td> <!-- Bootstrap badge a jobb vizualitásért -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center">Nincsenek korábbi bérléseid.</p>
        <?php endif; ?>
        <?php $stmt_berles->close(); // Statement lezárása 
        ?>
    </div>
    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>
    <?php
    // Modal megjelenítése, ha szükséges (általában a body végén)
    if ($showModal) {
        include 'modal.php'; // Feltételezve, hogy a modal.php tartalmazza a #alertModal-t
        // És a modal.php kiírja az $_SESSION['alert_message'] tartalmát
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Ellenőrizd, hogy a modal.php-ban mi a modal ID-ja
                var alertModal = new bootstrap.Modal(document.getElementById("alertModal"));
                if(alertModal) {
                    alertModal.show();
                }
            });
          </script>';
        // Üzenet törlése a session-ből, miután feldolgoztuk
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
    }
    ?>

    <!-- Bootstrap JS (ha szükséges, pl. a modalhoz vagy más interaktív elemekhez) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // Menü toggle script (változatlan)
        const menuToggle = document.querySelector(".menu-toggle");
        const headerNav = document.querySelector("header nav"); // Pontosabb selector
        if (menuToggle && headerNav) {
            menuToggle.addEventListener("click", function() {
                // Használjunk egyértelműbb class nevet
                headerNav.classList.toggle("menu-visible");
                // A header helyett a nav-ot érdemes módosítani, vagy egy dedikált body class-t
                document.body.classList.toggle('nav-open'); // Opcionális: body class a stílushoz
            });
        }
        // A modal megnyitásához/bezárásához lehet, hogy szükséged van JS-re,
        // de a Bootstrap modal a data-bs-toggle/data-bs-target attribútumokkal is működik.
        // Ha egyedi openModal funkciót használsz, itt kell lennie.
        function openModal(modalId) {
            var myModal = new bootstrap.Modal(document.getElementById(modalId), {});
            myModal.show();
        }
    </script>

</body>

</html>
<?php
$db->close(); // Adatbázis kapcsolat lezárása
?>