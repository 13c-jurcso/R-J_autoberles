<?php
session_start();
include "./db_connection.php";
include "./adatLekeres.php";
if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}

// Jármű és felhasznalo lista lekérése
$jarmuvek = $db->query("SELECT * FROM jarmuvek");
$felhasznalok = $db->query("SELECT * FROM felhasznalo;");

// Jármű hozzáadása
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    // Alapadatok
    $felhasznalas_id = $_POST['felhasznalas_id'];
    $szerviz_id = $_POST['szerviz_id'];
    $gyarto = trim($_POST['gyarto']);
    $tipus = trim($_POST['tipus']);
    $motor = trim($_POST['motor']);
    $gyartasi_ev = $_POST['gyartasi_ev'];
    $leiras = trim($_POST['leiras']);
    $ar = $_POST['ar'];

    // --- Képkezelési változók ---
    // Fizikai mappa útvonala a szerveren (htdocs a gyökér)
    $image_folder_physical = $_SERVER['DOCUMENT_ROOT'] . '/R-J_autoberles/kepek/';
    // Webes elérési út prefix (ezt mentjük az adatbázisba)
    $image_folder_web_base = '/R-J_autoberles/kepek/';

    $kepek = []; // Ebben gyűjtjük a webes útvonalakat az adatbázishoz
    $sikeresFeltoltesOsszes = true; // Jelző, hogy minden kép feltöltése sikeres volt-e
    $upload_errors = []; // Hibák gyűjtése

    // Fájlnév előkészítése
    $safeGyarto = strtolower(preg_replace('/[^a-z0-9]/i', '', $gyarto));
    $safeTipus = strtolower(preg_replace('/[^a-z0-9]/i', '', $tipus));
    $baseFileName = $safeGyarto . $safeTipus;
    if (empty($baseFileName)) {
        $baseFileName = 'jarmu';
    }
    $kepSzamlalo = 0;

    // --- Képfeltöltés logikája ---
    if (isset($_FILES['kep_url']) && is_array($_FILES['kep_url']['name']) && !empty($_FILES['kep_url']['name'][0])) {

        // Ellenőrizzük/létrehozzuk a célmappát
        if (!is_dir($image_folder_physical)) {
            if (!@mkdir($image_folder_physical, 0775, true) && !is_dir($image_folder_physical)) {
                 $upload_errors[] = "Nem sikerült létrehozni a képek mappát: " . htmlspecialchars($image_folder_physical) . ". Ellenőrizze a jogosultságokat ('/php' mappára is kell írási jog)!";
                 $sikeresFeltoltesOsszes = false; // Ha a mappa sincs meg, nem tudunk feltölteni
            }
        } elseif (!is_writable($image_folder_physical)) {
             $upload_errors[] = "A képek mappa nem írható: " . htmlspecialchars($image_folder_physical);
             $sikeresFeltoltesOsszes = false; // Ha nem írható, nem tudunk feltölteni
        }

        // Csak akkor megyünk tovább, ha a mappa rendben van
        if ($sikeresFeltoltesOsszes) {
            foreach ($_FILES['kep_url']['name'] as $key => $kep_name) {
                if ($_FILES['kep_url']['error'][$key] === UPLOAD_ERR_OK && !empty($kep_name)) {
                    $fileTmpPath = $_FILES['kep_url']['tmp_name'][$key];
                    $file_size = $_FILES['kep_url']['size'][$key];

                     // --- Biztonsági ellenőrzések ---
                     $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                     $max_size = 5 * 1024 * 1024; // 5MB
                     $file_mime_type = mime_content_type($fileTmpPath);

                    if (!in_array(strtolower($file_mime_type), $allowed_types)) {
                        $upload_errors[] = "Fájl ('".htmlspecialchars($kep_name)."') típusa ('".htmlspecialchars($file_mime_type)."') nem engedélyezett.";
                        $sikeresFeltoltesOsszes = false;
                        break; // Hiba esetén megszakítjuk a ciklust
                    }
                     if ($file_size > $max_size) {
                        $upload_errors[] = "Fájl ('".htmlspecialchars($kep_name)."') mérete túl nagy (max 5MB).";
                        $sikeresFeltoltesOsszes = false;
                        break;
                    }
                    // --- Ellenőrzések vége ---

                    $extension = strtolower(pathinfo($kep_name, PATHINFO_EXTENSION));
                    $kepSzamlalo++;
                    // Az egyedi, tisztított fájlnév
                    $fileName = $baseFileName . '_' . $kepSzamlalo . '.' . $extension;

                    // Cél útvonal a FIZIKAI mentéshez
                    $destination_path_physical = $image_folder_physical . $fileName;
                    // Webes útvonal az ADATBÁZISHOZ és megjelenítéshez
                    $web_path_for_db = $image_folder_web_base . $fileName;

                    // Fájl mozgatása a fizikai helyre
                    if (move_uploaded_file($fileTmpPath, $destination_path_physical)) {
                        // A HELYES webes útvonalat adjuk hozzá a tömbhöz
                        $kepek[] = $web_path_for_db;
                        error_log("Sikeres move_uploaded_file: " . $fileName . " -> " . $destination_path_physical . ". Hozzáadva DB-hez: " . $web_path_for_db);
                    } else {
                        $upload_errors[] = "Hiba történt a(z) '" . htmlspecialchars($kep_name) . "' fájl mozgatásakor ide: " . htmlspecialchars($destination_path_physical);
                        $sikeresFeltoltesOsszes = false;
                        error_log("!!! Sikertelen move_uploaded_file: " . $fileName . " ide: " . $destination_path_physical . ". PHP hiba: " . print_r(error_get_last(), true));
                        break; // Hiba esetén megszakítjuk
                    }
                } else if ($_FILES['kep_url']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $upload_errors[] = "Fájlfeltöltési hiba a(z) '" . htmlspecialchars($kep_name) . "' fájlnál. Kód: " . $_FILES['kep_url']['error'][$key];
                    $sikeresFeltoltesOsszes = false;
                    error_log("!!! Fájlfeltöltési hiba (nem NO_FILE): " . htmlspecialchars($kep_name) . ". Kód: " . $_FILES['kep_url']['error'][$key]);
                    break; // Hiba esetén megszakítjuk
                }
            } // foreach ciklus vége
        } // if mappa rendben van vége

    } else {
         // Ha a 'kep_url' nincs beállítva vagy üres, de kötelező volt a formon ('required')
         if (isset($_FILES['kep_url']['error'][0]) && $_FILES['kep_url']['error'][0] === UPLOAD_ERR_NO_FILE) {
            $upload_errors[] = "Nem töltött fel egyetlen képet sem, pedig kötelező.";
            $sikeresFeltoltesOsszes = false; // Nincs kép, nincs mit menteni
         } else {
            // Egyéb hiba a $_FILES struktúrával
             $upload_errors[] = "Hiba a fájlfeltöltési adatok feldolgozásakor.";
             $sikeresFeltoltesOsszes = false;
             error_log("!!! Hiba a FILES tömb struktúrájával: " . print_r($_FILES, true));
         }
    }

    // ---- Adatbázis művelet ----
    // Csak akkor próbálunk beszúrni, ha minden kép feltöltése sikeres volt ÉS van legalább egy kép
    if ($sikeresFeltoltesOsszes && count($kepek) > 0) {
        $kepek_json = json_encode($kepek); // A $kepek már a /php/kepek/... útvonalakat tartalmazza
        error_log("Adatbázis INSERT előkészítése. Képek JSON: " . $kepek_json);

        $stmt_insert = $db->prepare("INSERT INTO jarmuvek (felhasznalas_id, szerviz_id, gyarto, tipus, motor, gyartasi_ev, leiras, ar, kep_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt_insert === false) {
             error_log("!!! Adatbázis prepare() HIBA: " . $db->error);
             $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az adatbázis művelet előkészítésekor!</div>';
        } else {
            $stmt_insert->bind_param("iisssssis", $felhasznalas_id, $szerviz_id, $gyarto, $tipus, $motor, $gyartasi_ev, $leiras, $ar, $kepek_json);
            if ($stmt_insert->execute()) {
                 error_log("Adatbázis execute() SIKER. Beszúrt ID: " . $stmt_insert->insert_id);
                 $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Jármű sikeresen hozzáadva!</div>';
            } else {
                 error_log("!!! Adatbázis execute() HIBA: " . $stmt_insert->error);
                 // Részletesebb hibaüzenet fejlesztéshez
                 $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a jármű adatbázisba mentése során! Részletek: '.htmlspecialchars($stmt_insert->error).'</div>';

                 // Próbáljuk meg törölni a feltöltött képeket, ha a DB mentés nem sikerült
                  foreach ($kepek as $web_path_to_delete) {
                      $physical_path_to_delete = $_SERVER['DOCUMENT_ROOT'] . $web_path_to_delete;
                      if (file_exists($physical_path_to_delete)) {
                          @unlink($physical_path_to_delete);
                      }
                  }
            }
            $stmt_insert->close();
        }

    } else {
        // Ha a feltöltés sikertelen volt, vagy nem volt kép
         $hiba_uzenet = "A jármű hozzáadása sikertelen volt.";
         if (!empty($upload_errors)) {
             $hiba_uzenet .= " Hibák: " . implode('; ', $upload_errors);
         } elseif (empty($kepek)) {
             // Ez az ág akkor futhat, ha a required ellenére nem töltöttek fel fájlt, vagy hiba volt a $_FILES-ban
             $hiba_uzenet .= " Nem történt képfeltöltés vagy hiba történt a fájlok feldolgozása közben.";
         }
         $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">' . htmlspecialchars($hiba_uzenet) . '</div>';
         error_log("Adatbázis INSERT kihagyva. Sikeres összes feltöltés: " . ($sikeresFeltoltesOsszes?'igen':'nem') . ", Képek száma: " . count($kepek) . ". Hibák: " . implode('; ', $upload_errors));
    }

    // Átirányítás a PRG (Post-Redirect-Get) minta alapján, hogy ne lehessen újraküldeni a formot frissítéssel
    header("Location: autok_kezeles.php");
    exit();
}

// Jármű törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_vehicle']) && isset($_POST['jarmu_id'])) {
    $jarmu_id = $_POST['jarmu_id'];

    // Biztonsági okokból ellenőrizd, hogy a $jarmu_id tényleg szám
    if (is_numeric($jarmu_id)) {
        $db->begin_transaction(); // Tranzakció indítása

        // Először töröld a bérléseket
        $sql_delete_berlesek = "DELETE FROM berlesek WHERE jarmu_id = ?";
        $stmt_delete_berlesek = $db->prepare($sql_delete_berlesek);
        $stmt_delete_berlesek->bind_param("i", $jarmu_id);

        if ($stmt_delete_berlesek->execute()) {
            //Ha sikeresen törlődtek a bérlések, kitöröljük az akciókbol is.
            $sql_delete_akciok = "DELETE FROM `akciok` WHERE jarmu_id = ?";
            $stmt_delete_akciok = $db->prepare($sql_delete_akciok);
            $stmt_delete_akciok->bind_param("i", $jarmu_id);

            if($stmt_delete_akciok->execute()){
                //Ha az akciók törlése sikeres, kitöröljük a véleményektől.
                $sql_delete_velemeny = "DELETE FROM `velemenyek` WHERE jarmu_id = ?";
                $stmt_delete_velemeny = $db->prepare($sql_delete_velemeny);
                $stmt_delete_velemeny->bind_param("i", $jarmu_id);

                if($stmt_delete_velemeny->execute()){
                    // Ha a bérlések törlése sikerült, akkor töröld a járművet
                    $sql_delete_jarmuvek = "DELETE FROM jarmuvek WHERE jarmu_id = ?";
                    $stmt_delete_jarmuvek = $db->prepare($sql_delete_jarmuvek);
                    $stmt_delete_jarmuvek->bind_param("i", $jarmu_id);

                    if ($stmt_delete_jarmuvek->execute()) {
                        $db->commit(); // Ha minden sikerült, véglegesítsd a tranzakciót
                        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Jármű sikeresen törölve!</div>';
                    } else {
                        $db->rollback(); // Hiba esetén görgess vissza
                        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a jármű törlése során!</div>';
                        error_log("Hiba a jármű törlése során: " . $stmt_delete_jarmuvek->error);
                    }
                    $stmt_delete_jarmuvek->close();
                }
                else{
                    $db->rollback(); // Hiba esetén görgess vissza
                    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba, a jármű törlése során, a vélemények táblából!</div>';
                    error_log("Hiba a vélemények törlése során: " . $stmt_delete_akciok->error);
                }
                $stmt_delete_velemeny->close();
            }
            else{
                $db->rollback(); // Hiba esetén görgess vissza
                $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba, a jármű törlése során, az akciók táblából!</div>';
                error_log("Hiba az akciók törlése során: " . $stmt_delete_akciok->error);
            }
            $stmt_delete_akciok->close();
        } else {
            $db->rollback(); // Hiba esetén görgess vissza
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba, a jármű törlése során, a bérlések táblából!</div>';
            error_log("Hiba a bérlések törlése során: " . $stmt_delete_berlesek->error);
        }
        $stmt_delete_berlesek->close();

    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-warning" role="alert">Érvénytelen jármű ID!</div>';
    }

    header("Location: autok_kezeles.php"); // Átirányítás a lap tetejére a törlés után
    exit();
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vezérlőpult</title>
    <link rel="icon" href="../../admin_favicon.png" type="image/png">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
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
    <div>
        <!-- Üzenetek -->
        <?php
            // session_start();
            if (isset($_SESSION['uzenet'])) {
                echo $_SESSION['uzenet'];
                unset($_SESSION['uzenet']);
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
                <option value="">-- Kérem válasszon --</option>
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

            <label for="szerviz_id">Műszaki lejárat:(Csak év)</label>
            <input type="number" name="szerviz_id" required><br>

            <label for="gyartasi_ev">Gyártási év:</label>
            <input type="date" name="gyartasi_ev" required><br>

            <label for="leiras">Leírás:</label>
            <textarea name="leiras" required></textarea><br>

            <label for="ar">Ár:</label>
            <input type="number" name="ar" required><br>

            <label for="kep_url">Képek:</label>
            <div class="mb-3">
                <input type="file" class="form-control" id="new_images" name="kep_url[]" accept="image/jpeg, image/png, image/gif, image/webp" multiple>
                <div class="form-text">Engedélyezett formátumok: JPG, PNG, GIF, WEBP. Max méret: 5MB / kép.</div>
            </div>

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
                    <form method="POST" action="" onsubmit="return confirm('Biztosan törölni kívánja az autót?');">
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

  

    <div id="overlay" class="overlay"></div>
    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>
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