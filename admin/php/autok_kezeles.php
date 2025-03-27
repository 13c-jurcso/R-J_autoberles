<?php
include "./db_connection.php";
include "./adatLekeres.php";

// Jármű és felhasznalo lista lekérése
$jarmuvek = $db->query("SELECT * FROM jarmuvek");
$felhasznalok = $db->query("SELECT * FROM felhasznalo;");

// Jármű hozzáadása
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    $felhasznalas_id = $_POST['felhasznalas_id'];
    $szerviz_id = $_POST['szerviz_id'];
    $gyarto = $_POST['gyarto'];
    $tipus = $_POST['tipus'];
    $motor = $_POST['motor'];
    $gyartasi_ev = $_POST['gyartasi_ev'];
    $leiras = $_POST['leiras'];
    $ar = $_POST['ar'];

    $kepmappa = "../../php/kepek/";
    $kepek = []; // Ez egy tömb, amely a képek elérési útvonalait tartalmazza.

    // Több kép feltöltése
    foreach ($_FILES['kep_url']['name'] as $key => $kep_name) {
        $fileTmpPath = $_FILES['kep_url']['tmp_name'][$key];
        $fileName = basename($kep_name);
        $filePath = $kepmappa . $fileName;

        if (move_uploaded_file($fileTmpPath, $filePath)) {
            $kepek[] = $filePath; // A sikeresen feltöltött képek elérési útvonalát hozzáadjuk a tömbhöz.
        }
    }

    // Ha legalább egy kép sikeresen feltöltésre került, azokat elmenthetjük
    if (count($kepek) > 0) {
        // Képek tárolása az adatbázisban (JSON formátumban tároljuk)
        $kepek_json = json_encode($kepek);

        // Jármű adatainak beszúrása
        $modositas = $db->prepare("INSERT INTO jarmuvek (felhasznalas_id, szerviz_id, gyarto, tipus, motor, gyartasi_ev, leiras, ar, kep_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $modositas->bind_param("iisssssis", $felhasznalas_id, $szerviz_id, $gyarto, $tipus, $motor, $gyartasi_ev, $leiras, $ar, $kepek_json);

        if ($modositas->execute()) {
            session_start();
            $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Sikeres hozzáadás!</div>';
        } else {
            echo '<div class="sikertelen" id="animDiv">Hiba a hozzáadás során!</div>';
            var_dump($modositas->error);
        }
        $modositas->close();
    } else {
        echo '<div class="sikertelen" id="animDiv">Nem sikerült képeket feltölteni.</div>';
    }
}

// Jármű törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_vehicle'])) {
    $jarmu_id = $_POST['jarmu_id'];

    $torles = $db->prepare("DELETE FROM jarmuvek WHERE jarmu_id = ?");
    $torles->bind_param("i", $jarmu_id);

    if ($torles->execute()) {
        $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Sikeres hozzáadás!</div>';
    } else {
        echo '<div class="sikertelen" id="animDiv">Hiba a törlés során!</div>';
        var_dump($torles->error);
    }
    $torles->close();
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vezérlőpult</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    <h1>Vezérlőpult</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek" onclick="mutatResz('resz1')">Járművek
        </button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag" onclick="mutatResz('resz2')">Jogosultságok 
        </button></a>
        <a href="./admin_berlesek.php"><button type="submit" id="berlesek">Bérlések 
        </button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
    <hr>
    <div>
        <!-- Üzenetek -->
        <?php
            // session_start();
            if (isset($_SESSION['uzenet'])) {
                echo $_SESSION['uzenet'];
            }
        ?>
    </div>

    <div id="hozzaad_jarmuvek" class="tartalmi-resz">
        <h2>Jármű hozzáadása</h2>
        <form method="POST" enctype="multipart/form-data" class="form">
            <label for="gyarto">Gyártó:</label>
            <input type="text" name="gyarto" required><br>

            <label for="tipus">Típus:</label>
            <input type="text" name="tipus" required><br>

            <label for="motor">Motor:</label>
            <input type="text" name="motor" required><br>

            <label for="felhasznalas_id">Felhasználási mód:</label>
            <select name="felhasznalas_id">
                <?php
                    $felhasznalas_sql = "SELECT felhasznalas.felhasznalas_id, felhasznalas.nev FROM felhasznalas;";
                    $felhasznalas = adatokLekerese($felhasznalas_sql);
                    if (is_array($felhasznalas)) {
                        foreach ($felhasznalas as $f) {
                            echo '<option value="'. $f['felhasznalas_id'].'">' . $f['nev'] . '</option>'; 
                        }
                    } else {
                        echo $felhasznalas;
                    }
                ?>
            </select>

            <label for="szerviz_id">Szerviz ID:</label>
            <input type="number" name="szerviz_id" required><br>

            <label for="gyartasi_ev">Gyártási év:</label>
            <input type="date" name="gyartasi_ev" required><br>

            <label for="leiras">Leírás:</label>
            <input type="text" name="leiras" required><br>

            <label for="ar">Ár:</label>
            <input type="number" name="ar" required><br>

            <label for="kep_url">Képek:</label>
            <input type="file" name="kep_url[]" accept="image/*" multiple required><br>

            <button type="submit" name="add_vehicle">Hozzáadás</button>
        </form>
    </div>
    <hr>

    <div id="torles_jarmuvek" class="tartalmi-resz">
        <!-- Jármű törlése és módosítás -->
        <h2>Járművek Törlése</h2>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Felhasználás ID</th>
                <th>Szerviz ID</th>
                <th>Gyártó</th>
                <th>Típus</th>
                <th>Motor</th>
                <th>Gyártási év</th>
                <th>Leírás</th>
                <th>Ár</th>
                <th></th>
                <th>Művelet</th>
            </tr>
            <?php if ($jarmuvek->num_rows > 0): ?>
            <?php while ($row = $jarmuvek->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['jarmu_id']; ?></td>
                <td><?php echo $row['felhasznalas_id']; ?></td>
                <td><?php echo $row['szerviz_id']; ?></td>
                <td><?php echo $row['gyarto']; ?></td>
                <td><?php echo $row['tipus']; ?></td>
                <td><?php echo $row['motor']; ?></td>
                <td><?php echo $row['gyartasi_ev']; ?></td>
                <td><?php echo $row['leiras']; ?></td>
                <td><?php echo $row['ar']; ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="jarmu_id" value="<?php echo $row['jarmu_id']; ?>">
                        <a href="./autok_kezeles_modositas.php?id=<?= $row['jarmu_id'] ?>"><button type="button"  class="modositas_button">Módosítás</button></a>
                    </form>
                </td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="jarmu_id" value="<?php echo $row['jarmu_id']; ?>">
                        <button type="submit" class="torles_button" name="delete_vehicle">Törlés</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
                <td colspan="10">Nincs jármű az adatbázisban.</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- EGYNLORE KIVESZEM, HOGY MUKODJON -->

    <!-- Törlésre figyelmeztető modális ablak -->
    <!-- <div id="csoo" class="modal">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="red" style="margin-top: 12px" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                        </svg>
                    </div>
                    <h4 class="modal-title w-100">Figyelem!</h4>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <h3>Biztos benne, högy törölni kívánja az elemet?</h3>
                    <p>Ez a művelet nem visszavonható.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal()">Mégse</button>
                    <form method="POST">
                        <button type="submit" class="btn btn-danger" name="delete_vehicle">Törlés</button>
                    </form>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Módosítás modális ablaka -->
    <!-- <div id="modositas" class="modal">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2 class="modal-title w-100">Módosítás</h2>
                </div>
                <div class="modal-body">
                    <form action="" class="form">
                
                    </form>
                </div>
            </div>
        </div>
    </div> -->


    

    <div id="overlay" class="overlay"></div>

    <script>
    function mutatResz(reszAzonosito, gomb) {
        // Az összes tartalmi rész rejtése
        const tartalmiReszek = document.querySelectorAll('.tartalmi-resz');
        tartalmiReszek.forEach(resz => resz.classList.remove('aktiv'));

        // Csak az adott rész megjelenítése
        const aktivResz = document.getElementById(reszAzonosito);
        if (aktivResz) {
            aktivResz.classList.add('aktiv');
        }

        // Az összes gombról eltávolítjuk az "aktiv" osztályt
        const gombok = document.querySelectorAll('.menu button');
        gombok.forEach(g => g.classList.remove('aktiv'));

        // Az aktuális gombhoz hozzáadjuk az "aktiv" osztályt
        gomb.classList.add('aktiv');
    }

    document.getElementById("animDiv").addEventListener("click", function() {
        this.classList.add("hidden");
    });

    </script>
</body>

</html>

<?php
$db->close();
?>