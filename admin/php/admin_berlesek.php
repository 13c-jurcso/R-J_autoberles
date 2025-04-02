<?php
session_start(); // Session indítása az üzenetekhez

// Adatbázis kapcsolat és segédfüggvények
include "./db_connection.php";
// Feltételezzük, hogy van egy adatLekerese függvény vagy használunk direkt SQL-t
// include "./adatLekerese.php"; // Ha használod
if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}
// Ellenőrizzük a DB kapcsolatot
if (!isset($db) || $db->connect_error) {
    $error_msg = isset($db) ? $db->connect_error : 'A $db kapcsolat objektum nem jött létre.';
    // Itt nem 'die', hanem hibaüzenetet jelenítünk meg a HTML-ben
    $db_hiba = "Adatbázis kapcsolati hiba: " . htmlspecialchars($error_msg);
} else {
    $db_hiba = null; // Nincs hiba
}

// --- Bérlések lekérdezése a táblázathoz ---
$berlesek = []; // Alapértelmezett üres tömb
$fetch_error = null; // Hibaüzenet a lekérdezéshez

if (!$db_hiba) { // Csak akkor próbálkozunk, ha van kapcsolat
    // SQL lekérdezés JOIN-okkal a kapcsolódó adatokhoz (jármű gyártó/típus, felhasználó név)
    // Fontos: A felhasználók táblájának és oszlopneveinek helyesnek kell lenniük!
    // Példa: feltételezzük, hogy van `felhasznalok` tábla `felhasznalo_id`-vel és `nev`-vel.
    $sql = "SELECT
                b.berles_id, b.tol, b.ig,
                j.gyarto, j.tipus,
                f.nev AS felhasznalo_teljes_nev, -- A 'felhasznalo' tábla 'nev' oszlopa (teljes név)
                b.felhasznalo AS berlo_felhasznalonev, -- A 'berlesek' tábla 'felhasznalo' oszlopa (felhasználónév)
                b.jarmu_id
            FROM berlesek b
            LEFT JOIN jarmuvek j ON b.jarmu_id = j.jarmu_id
            LEFT JOIN felhasznalo f ON b.felhasznalo = f.felhasznalo_nev -- JOIN a 'felhasznalo' táblához a felhasználónév alapján
            ORDER BY b.tol DESC";

$result = $db->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $berlesek[] = $row;
        }
    }
    $result->free();
} else {
    $fetch_error = "Hiba a bérlések lekérdezése során: " . htmlspecialchars($db->error);
    error_log("SQL Hiba (bérlések lekérdezése): " . $db->error);
}
}

?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../admin_favicon.png" type="image/png">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Bérlések Kezelése</title>
    
</head>

<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="../../php/index.php">Főoldal</a></li>
                <li><a href="../../php/husegpontok.php">Hűségpontok</a></li>
                <li><a href="../../php/jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>

    <div class="container mt-4">
        <h1>Bérlések Kezelése</h1>

        <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek">Járművek
        </button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag">Jogosultságok 
        </button></a>
        <a href="./admin_berlesek.php"><button type="submit" id="berlesek">Bérlések 
        </button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
        <hr>

        <!-- Üzenetek helye -->
        <div id="uzenet-container">
            <?php
            if (isset($_SESSION['uzenet'])) {
                // A session üzenet már tartalmazza a teljes alert div-et
                echo $_SESSION['uzenet'];
                unset($_SESSION['uzenet']); // Üzenet törlése a megjelenítés után
            }
            if ($db_hiba) {
                 echo '<div class="alert alert-danger" role="alert">' . $db_hiba . '</div>';
            }
            if ($fetch_error) {
                 echo '<div class="alert alert-warning" role="alert">' . $fetch_error . '</div>';
            }
            ?>
        </div>

        
            <!-- Új bérlés form -->
           
                 <h2>Új bérlés hozzáadása</h2>
                 <!-- Az action az API kezelőre mutat -->
                 <form action="./api.php" method="POST" class="form" novalidate>
                     <!-- Nincs szükség _method-ra POST esetén -->
                     
                         <label for="jarmu_id" class="form-label">Jármű ID:</label>
                         <input type="number"  id="jarmu_id" name="jarmu_id" required min="1">
                         <div class="invalid-feedback">Jármű ID megadása kötelező.</div>
                    
                         <label for="felhasznalo_id" class="form-label">Felhasználó ID:</label>
                         <input type="number"  id="felhasznalo_id" name="felhasznalo_id" required min="1">
                          <div class="invalid-feedback">Felhasználó ID megadása kötelező.</div>
                   
                         <label for="tol" class="form-label">Átvétel időpontja:</label>
                         <input type="date" " id="tol" name="tol" required>
                          <div class="invalid-feedback">Átvétel dátumának megadása kötelező.</div>
                 
                         <label for="ig" class="form-label">Leadás dátuma:</label>
                         <input type="date"  id="ig" name="ig" required>
                          <div class="invalid-feedback">Leadás dátumának megadása kötelező.</div>
                     
                     <button type="submit" class="btn btn-success">Hozzáadás🆙</button>
                 </form>
           

            <!-- Bérlés módosítása form -->
            
                 <h2>Bérlés módosítása</h2>
                 <!-- Az action az API kezelőre mutat -->
                 <form action="./api.php" method="POST" class="form" novalidate>
                     <!-- Rejtett mező a PUT metódus jelzésére -->
                     <input type="hidden" name="_method" value="PUT">
                     
                         <label for="edit_berles_id" class="form-label">Módosítandó Bérlés ID:</label>
                         <input type="number"  id="edit_berles_id" name="berles_id" required min="1">
                          <div class="invalid-feedback">Módosítandó Bérlés ID megadása kötelező.</div>
                  
                         <label for="edit_jarmu_id" class="form-label">Új Jármű ID:</label>
                         <input type="number"  id="edit_jarmu_id" name="jarmu_id" required min="1">
                         <div class="invalid-feedback">Jármű ID megadása kötelező.</div>
                    
                         <label for="edit_felhasznalo_id" class="form-label">Új Felhasználó ID:</label>
                         <input type="number"  id="edit_felhasznalo_id" name="felhasznalo_id" required min="1">
                         <div class="invalid-feedback">Felhasználó ID megadása kötelező.</div>
                    
                         <label for="edit_tol" class="form-label">Új Átvétel időpontja:</label>
                         <input type="date"  id="edit_tol" name="tol" required>
                          <div class="invalid-feedback">Átvétel dátumának megadása kötelező.</div>
                    
                         <label for="edit_ig" class="form-label">Új Leadás dátuma:</label>
                         <input type="date"  id="edit_ig" name="ig" required>
                         <div class="invalid-feedback">Leadás dátumának megadása kötelező.</div>
                    
                     <button type="submit" class="btn btn-primary">Módosítás💾</button>
                 </form>
            
        
        <hr>

        <h2>Aktuális Bérlések</h2>
        <div class="table-container">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gyártó</th>
                        <th>Típus</th>
                        <th>Bérlő Neve</th>
                        <th>Átvétel</th>
                        <th>Leadás</th>
                        <th>Művelet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($berlesek)): ?>
                        <?php foreach ($berlesek as $berles): ?>
                            <tr>
                                <td><?= htmlspecialchars($berles['berles_id']) ?></td>
                                <td><?= htmlspecialchars($berles['gyarto'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($berles['tipus'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($berles['felhasznalo_teljes_nev'] ?? 'N/A') ?> (<?= htmlspecialchars($berles['berlo_felhasznalonev']) ?>)</td>
                                <td><?= htmlspecialchars($berles['tol']) ?></td>
                                <td><?= htmlspecialchars($berles['ig']) ?></td>
                                <td>
                                    <!-- Törlés form -->
                                    <form action="./api.php" method="POST" class="delete-form" onsubmit="return confirm('Biztosan törölni szeretné ezt a bérlést?');">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="berles_id" value="<?= htmlspecialchars($berles['berles_id']) ?>">
                                        <button type="submit" title="Törlés">Törlés🗑️</button> <!-- Kuka ikon -->
                                    </form>
                                    <!-- Opcionális: Módosítás link egy külön oldalra -->
                                    <!-- <a href="edit_berles_page.php?id=<?= htmlspecialchars($berles['berles_id']) ?>" class="btn btn-warning btn-sm" title="Módosítás">✎</a> -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif (!$fetch_error && !$db_hiba): ?>
                        <tr>
                            <td colspan="7" class="text-center">Nincsenek aktuális bérlések.</td>
                        </tr>
                    <?php else: ?>
                         <tr>
                            <td colspan="7" class="text-center text-danger">Hiba történt a bérlések betöltésekor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div> <!-- /.container -->

    <footer class="container mt-5 mb-3 text-center text-muted">
        R&J Admin - © <?= date('Y') ?>
    </footer>

    <!-- Bootstrap Bundle JS (csak ha kell pl. dropdownokhoz, de a validációhoz nem feltétlenül) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        // Egyszerű menü toggle (opcionális)
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('header nav');
        if (menuToggle && nav) {
            menuToggle.addEventListener('click', () => {
                nav.classList.toggle('active'); // CSS-ben kell definiálni az .active stílust
            });
        }

        // Egyszerű Bootstrap kliens oldali validáció inicializálása
         (() => {
           'use strict'
           const forms = document.querySelectorAll('.needs-validation')
           Array.from(forms).forEach(form => {
             form.addEventListener('submit', event => {
               if (!form.checkValidity()) {
                 event.preventDefault()
                 event.stopPropagation()
               }
               form.classList.add('was-validated')
             }, false)
           })
         })()
    </script>

</body>
</html>