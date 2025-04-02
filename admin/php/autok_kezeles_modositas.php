<?php
session_start();

include "./db_connection.php"; // Feltételezem, hogy ez $db néven hozza létre a kapcsolatot
include "./adatLekeres.php"; // Feltételezem, hogy ez tartalmazza az adatokLekerese funkciót

if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}


// --- Modal include helyett közvetlen üzenetkezelés ---

// --- Üzenetkezelés vége ---


// Ellenőrizzük, hogy a $db objektum létezik-e (a db_connection.php-ból)
if (!isset($db) || $db->connect_error) {
    // Adjunk informatívabb hibaüzenetet
    $error_msg = isset($db) ? $db->connect_error : 'A $db kapcsolat objektum nem jött létre.';
    die("Adatbázis kapcsolati hiba: " . htmlspecialchars($error_msg)); // htmlspecialchars hozzáadva
}

$kocsiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kocsiId <= 0) {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Érvénytelen jármű ID.</div>';
    header("Location: autok_kezeles.php"); // Visszairányítás, ha nincs ID
    exit;
}

// --- Jármű Adatainak Lekérdezése ---
$stmt_car = $db->prepare("SELECT * FROM jarmuvek WHERE jarmu_id = ?");
// Hibaellenőrzés prepare után
if ($stmt_car === false) {
    // Naplózás ajánlott éles környezetben
    error_log("SQL prepare hiba (jármű lekérdezés): " . $db->error);
    die("Hiba történt a művelet előkészítésekor. Kérjük, próbálja meg később."); // Felhasználóbarát üzenet
}
$stmt_car->bind_param("i", $kocsiId);
if (!$stmt_car->execute()) {
     // Naplózás ajánlott éles környezetben
     error_log("SQL execute hiba (jármű lekérdezés): " . $stmt_car->error);
     die("Hiba történt az adatok lekérésekor. Kérjük, próbálja meg később."); // Felhasználóbarát üzenet
}
$result_car = $stmt_car->get_result();

if ($result_car->num_rows > 0) {
    $car = $result_car->fetch_assoc();
    // Képek JSON dekódolása PHP tömbbé
    $car_images = json_decode($car['kep_url'] ?? '[]', true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Hibakezelés, ha a JSON érvénytelen
        $car_images = [];
        // Adjunk erről is jelzést (lehetne session üzenet is, de most marad a korábbi logika)
        // Fontos: Ha itt beállítjuk a $_SESSION['uzenet']-et, egy későbbi sikeres mentés felülírhatja
        // Jobb lenne ezt a hibát is a mentés utáni üzenethez fűzni, vagy külön logolni.
        // Most nem változtatok ezen a kérésnek megfelelően.
    }
    // Biztosítjuk, hogy tömb legyen
    if (!is_array($car_images)) {
        $car_images = [];
    }

} else {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">A keresett jármű nem található.</div>';
    header("Location: autok_kezeles.php");
    exit;
}
$stmt_car->close();


// --- Jármű Módosítása (POST kérés feldolgozása) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vehicle'])) {
    $jarmu_id = $_POST['jarmu_id'] ? (int)$_POST['jarmu_id'] : 0;

    // Egyszerű CSRF védelem (ellenőrzi, hogy a formban lévő ID megegyezik-e a GET paraméterével)
    if ($jarmu_id !== $kocsiId) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Biztonsági hiba: Azonosító eltérés.</div>';
        // Fontos lehet naplózni az ilyen eseteket
        error_log("CSRF gyanú vagy ID eltérés: GET ID=" . $kocsiId . ", POST ID=" . $jarmu_id);
        header("Location: autok_kezeles.php");
        exit;
    }

    // Alapadatok kinyerése és tisztítása
    $felhasznalas_id = filter_input(INPUT_POST, 'felhasznalas_id', FILTER_VALIDATE_INT);
    $szerviz_id = filter_input(INPUT_POST, 'szerviz_id', FILTER_VALIDATE_INT); // Ha lehet üres, akkor más filter kellhet
    $gyarto = trim(filter_input(INPUT_POST, 'gyarto', FILTER_SANITIZE_SPECIAL_CHARS));
    $tipus = trim(filter_input(INPUT_POST, 'tipus', FILTER_SANITIZE_SPECIAL_CHARS));
    $motor = trim(filter_input(INPUT_POST, 'motor', FILTER_SANITIZE_SPECIAL_CHARS));
    $gyartasi_ev = filter_input(INPUT_POST, 'gyartasi_ev'); // Dátum validálása később
    $leiras = trim(filter_input(INPUT_POST, 'leiras', FILTER_SANITIZE_SPECIAL_CHARS));
    $ar = filter_input(INPUT_POST, 'ar', FILTER_VALIDATE_FLOAT); // Vagy FILTER_VALIDATE_INT, ha egész szám

    // Alap validációk (példák, bővíthető)
    $errors = [];
    if (empty($gyarto)) $errors[] = "A gyártó megadása kötelező.";
    if (empty($tipus)) $errors[] = "A típus megadása kötelező.";
    if ($felhasznalas_id === false || $felhasznalas_id <= 0) $errors[] = "Érvénytelen felhasználási mód.";
    if ($ar === false || $ar < 0) $errors[] = "Érvénytelen ár.";
    // Dátum validálása (YYYY-MM-DD formátum ellenőrzése)
    if (!empty($gyartasi_ev) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $gyartasi_ev)) {
        $errors[] = "Érvénytelen gyártási év formátum (YYYY-MM-DD szükséges).";
    } elseif (!empty($gyartasi_ev)) {
        // Opcionális: Érvényes dátum-e (pl. nem 2023-02-30)
        $d = DateTime::createFromFormat('Y-m-d', $gyartasi_ev);
        if (!$d || $d->format('Y-m-d') !== $gyartasi_ev) {
            $errors[] = "Érvénytelen dátum a gyártási év mezőben.";
        }
    } else {
        // Ha a gyártási év nem kötelező, és üresen érkezik, állítsuk NULL-ra vagy üres stringre a DB típustól függően
        $gyartasi_ev = null; // Vagy $gyartasi_ev = '';
    }


    // Ha vannak validációs hibák az alap adatokkal, ne folytassuk a képkezelést és DB műveletet
    if (!empty($errors)) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bevitt adatokban:<ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
        // Nem irányítunk át, hogy a felhasználó lássa a hibákat és a kitöltött űrlapot
        // A $car és $car_images változók még a GET kérésből származó adatokat tartalmazzák, így az űrlap újratöltődik velük.
    } else {
        // --- Képkezelés (csak ha nincsenek alap adat hibák) ---
        $current_images = $car_images; // A DB-ből betöltött képek listája
        $updated_images = []; // Az új képlista inicializálása
        $upload_errors = [];

        $image_folder_physical = $_SERVER['DOCUMENT_ROOT'] . '/berles/kepek/';
        $image_folder_web_base = '/berles/kepek/';

        // 1. Képek törlése
        $images_to_delete = isset($_POST['delete_image']) ? $_POST['delete_image'] : [];
        if (!is_array($images_to_delete)) $images_to_delete = [];

        foreach ($current_images as $img_web_path) {
            if (!in_array($img_web_path, $images_to_delete)) {
                $updated_images[] = $img_web_path;
            } else {
                $physical_path_to_delete = $_SERVER['DOCUMENT_ROOT'] . $img_web_path;
                if (file_exists($physical_path_to_delete)) {
                    if (!@unlink($physical_path_to_delete)) {
                        $upload_errors[] = "Nem sikerült törölni: " . htmlspecialchars(basename($img_web_path));
                        // Ha nem sikerül törölni, biztonsági okból megtarthatjuk a listában, hogy ne tűnjön el a DB-ből se
                        $updated_images[] = $img_web_path;
                        error_log("Fájl törlési hiba: " . $physical_path_to_delete);
                    }
                } else {
                     // Nem feltétlenül hiba, ha már nem létezik a fájl
                }
            }
        }

        // 2. Új képek feltöltése
        if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name']) && !empty($_FILES['new_images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!is_dir($image_folder_physical)) {
                 if (!@mkdir($image_folder_physical, 0775, true) && !is_dir($image_folder_physical)) {
                     $upload_errors[] = "Képek mappa létrehozása sikertelen. Jogosultságok ellenőrzése szükséges.";
                     error_log("Nem sikerült létrehozni a képek mappát: " . $image_folder_physical);
                 }
             }

            if (is_dir($image_folder_physical) && is_writable($image_folder_physical)) {
                foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['new_images']['name'][$key];
                        $file_size = $_FILES['new_images']['size'][$key];
                        $file_tmp = $_FILES['new_images']['tmp_name'][$key];

                        // MIME típus ellenőrzése biztonságosan
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $file_tmp);
                        finfo_close($finfo);

                        if (!in_array(strtolower($mime_type), $allowed_types)) {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Nem engedélyezett fájltípus (".htmlspecialchars($mime_type).").";
                            continue;
                        }
                        if ($file_size > $max_size) {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Túl nagy fájlméret (max 5MB).";
                            continue;
                        }

                        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                        // Tisztított, egyedi fájlnév
                        $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
                        $unique_filename = 'car_'.$jarmu_id.'_' . $safe_basename . '_' . uniqid('', true) . '.' . strtolower($file_extension);

                        $destination_path_physical = $image_folder_physical . $unique_filename;
                        $web_path_for_db = $image_folder_web_base . $unique_filename;

                        if (move_uploaded_file($file_tmp, $destination_path_physical)) {
                            $updated_images[] = $web_path_for_db;
                        } else {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Hiba a fájl áthelyezésekor.";
                            error_log("move_uploaded_file hiba: " . $file_name . " -> " . $destination_path_physical);
                        }
                    } elseif ($_FILES['new_images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                        // https://www.php.net/manual/en/features.file-upload.errors.php
                        $upload_errors[] = "Feltöltési hiba (" . htmlspecialchars($_FILES['new_images']['name'][$key]) . "): kód " . $_FILES['new_images']['error'][$key];
                    }
                }
            } else {
                 $upload_errors[] = "A képek mappa nem létezik vagy nem írható: " . htmlspecialchars($image_folder_physical);
                 error_log("Képek mappa nem létezik vagy nem írható: " . $image_folder_physical);
            }
        }

         // 3. Elsődleges kép beállítása
        $primary_image = isset($_POST['primary_image']) ? $_POST['primary_image'] : null;
        if ($primary_image && in_array($primary_image, $updated_images)) {
            $primary_key = array_search($primary_image, $updated_images);
            if ($primary_key !== false && $primary_key > 0) { // Csak akkor mozgatjuk, ha nem már az első
                $primary_item = array_splice($updated_images, $primary_key, 1)[0];
                array_unshift($updated_images, $primary_item);
            }
        } elseif (!empty($updated_images) && $primary_image === null) {
             // Ha nincs elsődleges kép expliciten kiválasztva, és vannak képek,
             // az első elem a listában lesz az elsődleges (ez az alapértelmezett viselkedés a jelenlegi logikával).
             // Ha a korábbi elsődlegest törölték, a következő kép lesz az elsődleges.
        }

        // --- Képkezelés VÉGE ---


        // Adatbázis frissítése
        // Üres tömb esetén is érvényes JSON ('[]') lesz
        $new_kep_url_json = json_encode(array_values($updated_images)); // Biztosítjuk a 0-tól indexelést

        $modositas = $db->prepare("UPDATE jarmuvek SET
                                    felhasznalas_id = ?, szerviz_id = ?, gyarto = ?,
                                    tipus = ?, motor = ?, gyartasi_ev = ?,
                                    leiras = ?, ar = ?, kep_url = ?
                                  WHERE jarmu_id = ?");

        if ($modositas === false) {
           // Naplózás + felhasználóbarát üzenet
           error_log("SQL prepare hiba (jármű frissítés): " . $db->error);
           $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a módosítás előkészítésekor.</div>';
        } else {
            // bind_param típusok: i=integer, d=double/float, s=string, b=blob
            $modositas->bind_param("iisssssdsi", // 'ar' lehet double/float ('d'), 'szerviz_id' integer ('i')
                                    $felhasznalas_id,
                                    $szerviz_id, // Ha a szerviz_id lehet NULL a DB-ben, akkor itt komplexebb logika kellhet
                                    $gyarto,
                                    $tipus,
                                    $motor,
                                    $gyartasi_ev, // $gyartasi_ev már string vagy null
                                    $leiras,
                                    $ar,
                                    $new_kep_url_json,
                                    $jarmu_id);

            if ($modositas->execute()) {
                // Siker esetén az alap üzenet
                 $alert_msg = "Sikeres módosítás!";
                 $alert_type = "success";

                // Ha voltak feltöltési hibák, jelezzük és módosítjuk az üzenet típusát
                if (!empty($upload_errors)) {
                     $alert_msg .= " Figyelem, képkezelési hibák történtek: <ul><li>" . implode('</li><li>', $upload_errors) . "</li></ul>";
                     $alert_type = 'warning'; // Típus módosítása warningra
                }

                // === EZ A FONTOS RÉSZ ===
                // Összeállítjuk a Bootstrap alert HTML-t és beletesszük a sessionbe
                $_SESSION['uzenet'] = '<div class="alert alert-' . htmlspecialchars($alert_type) . '" role="alert">' . $alert_msg . '</div>'; // Itt már nem kell htmlspecialchars az $alert_msg-re, mert a $upload_errors elemeit már escape-eltük, a többi része pedig biztonságos.

                // === Átirányítás PRG mintával (Ajánlott!) ===
                // Megakadályozza a dupla postolást frissítéskor
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $kocsiId . "&status=ok"); // Opcionális status paraméter
                exit;
                // Ha átirányítasz, az alábbi $car és $car_images frissítések feleslegesek itt,
                // mert az oldal újratöltésekor úgyis újra lekérdezi az adatokat a DB-ből.

            } else {
              // Naplózás + felhasználóbarát üzenet
              error_log("SQL execute hiba (jármű frissítés): " . $modositas->error);
              $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a módosítás során. Kérjük, próbálja meg később.</div>';
            }
            $modositas->close();
        }
    } // validációs hibák else ágának vége
} // POST kérés vége
?>


<!DOCTYPE html>
<html lang="hu"> <!-- Nyelv beállítása magyarra -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jármű Módosítása</title> <!-- Cím javítása -->
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Egyszerűbb stílusok a jobb átláthatóságért, ahogy eredetileg volt */
        .image-list { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1rem; }
        .image-item { border: 1px solid #ccc; padding: 5px; text-align: center; }
        .image-item img { max-width: 150px; height: auto; display: block; margin-bottom: 5px; }
        .image-item .controls label { display: block; margin-bottom: 3px; font-size: 0.9em; }
        .primary-indicator { font-weight: bold; color: green; font-size: 0.8em;}
        /* Eredeti form stílusok megtartása (ha voltak a .form class-hoz) */
        .form label { display: block; margin-top: 10px; }
        .form input[type=text],
        .form input[type=number],
        .form input[type=date],
        .form select,
        .form textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; box-sizing: border-box; }
        .menu button { padding: 8px 12px; } /* Vissza gomb stílusa */
    </style>
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="../../php/index.php">Főoldal</a></li>
                <li><a href="../../php/husegpontok.php">Hűségpontok</a></li>
                <li><a href="../../php/jarmuvek.php">Gépjárművek</a></li>
                <li><a href="./autok_kezeles.php">Járművek Kezelése</a></li>
            </ul>
        </nav>
    </header>
    <h1>Módosítás</h1>
    <hr>

    <div>
        <!-- Üzenetek -->
        <?php
            // Ellenőrizzük a $_SESSION['uzenet'] létezését
            if (isset($_SESSION['uzenet'])) {
                echo $_SESSION['uzenet'];
                // Töröljük az üzenetet a session-ből, hogy ne jelenjen meg újra (Flash message)
                unset($_SESSION['uzenet']);
            }
            // Opcionális: Ha átirányítás után status paramétert használunk
            if (isset($_GET['status']) && $_GET['status'] === 'ok' && !isset($_SESSION['uzenet'])) {
               // echo '<div class="alert alert-success" role="alert">Sikeres módosítás!</div>';
               // Ez már nem szükséges, mert a session üzenet kezeli a sikert is az átirányítás előtt.
            }
        ?>
    </div>

    <div class="menu">
         <!-- A gomb itt nem submit típusú, hanem egy link -->
        <a href="./autok_kezeles.php"><button type="button">Vissza a járművekhez</button></a>
    </div>

    <div id="jarmuvek_modositas" class="tartalmi-resz">

        <?php if (isset($car)): // Csak akkor jelenítjük meg a formot, ha $car létezik ?>
        <form method="POST" enctype="multipart/form-data" class="form" id="modositas-form" novalidate> <!-- novalidate a böngésző alapértelmezett hibaüzeneteinek kikapcsolásához, ha sajátot használsz -->

            <input type="hidden" name="jarmu_id" value="<?= htmlspecialchars($car['jarmu_id'] ?? '') ?>">

            <label for="gyarto">Gyártó:</label>
            <input type="text" id="gyarto" name="gyarto" value="<?= htmlspecialchars($_POST['gyarto'] ?? $car['gyarto'] ?? '') ?>" required><br> <!-- POST érték használata hiba esetén -->

            <label for="tipus">Típus:</label>
            <input type="text" id="tipus" name="tipus" value="<?= htmlspecialchars($_POST['tipus'] ?? $car['tipus'] ?? '') ?>" required><br>

            <label for="motor">Motor:</label>
            <input type="text" id="motor" name="motor" value="<?= htmlspecialchars($_POST['motor'] ?? $car['motor'] ?? '') ?>"><br>

            <label for="felhasznalas_id">Felhasználási mód:</label>
            <select name="felhasznalas_id" id="felhasznalas_id" required>
                 <!-- Üres opció a validációhoz -->
                 <option value="">-- Kérem válassz --</option>
                <?php
                    $felhasznalas_sql = "SELECT felhasznalas_id, nev FROM felhasznalas ORDER BY nev ASC;"; // Rendezés ABC szerint
                    $felhasznalas_modok = adatokLekerese($felhasznalas_sql);
                    $selected_felhasznalas = $_POST['felhasznalas_id'] ?? $car['felhasznalas_id'] ?? null; // POST érték hiba esetén

                    if (is_array($felhasznalas_modok)) {
                        foreach ($felhasznalas_modok as $f) {
                            // Összehasonlítás == típuskényszerítéssel, mert a POST string lehet, a DB int
                            $selected = ($selected_felhasznalas !== null && $f['felhasznalas_id'] == $selected_felhasznalas) ? 'selected' : '';
                            echo '<option value="'. htmlspecialchars($f['felhasznalas_id']).'" '.$selected.'>' . htmlspecialchars($f['nev']) . '</option>';
                        }
                    } else {
                        // Hibakezelés, ha a lekérdezés nem tömböt ad vissza
                         echo '<option value="" disabled>Hiba a módok betöltésekor.</option>';
                         error_log("Hiba adatokLekerese (felhasznalas): " . print_r($felhasznalas_modok, true));
                    }
                ?>
            </select><br>

            <label for="szerviz_id">Szerviz ID (opcionális):</label>
            <input type="number" id="szerviz_id" name="szerviz_id" value="<?= htmlspecialchars($_POST['szerviz_id'] ?? $car['szerviz_id'] ?? '') ?>" min="1"><br> <!-- min="1" ha csak pozitív ID lehet -->

            <label for="gyartasi_ev">Gyártási év (opcionális):</label>
            <?php
                // Formátum beállítása YYYY-MM-DD -ra, POST érték használata hiba esetén
                 $display_gyartasi_ev = $_POST['gyartasi_ev'] ?? $car['gyartasi_ev'] ?? '';
                 $gyartasi_ev_formatted = '';
                 if (!empty($display_gyartasi_ev)) {
                     try {
                         // Csak akkor formázzuk, ha már eleve nem a helyes formátumban van
                         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $display_gyartasi_ev)) {
                            $date_obj = new DateTime($display_gyartasi_ev);
                            $gyartasi_ev_formatted = $date_obj->format('Y-m-d');
                         } else {
                            $gyartasi_ev_formatted = $display_gyartasi_ev;
                         }
                     } catch (Exception $e) {
                         $gyartasi_ev_formatted = ''; // Hiba esetén üresen hagyjuk
                     }
                 }
            ?>
            <input type="date" id="gyartasi_ev" name="gyartasi_ev" value="<?= htmlspecialchars($gyartasi_ev_formatted) ?>"><br>

            <label for="leiras">Leírás:</label>
            <textarea name="leiras" id="leiras" rows="4"><?= htmlspecialchars($_POST['leiras'] ?? $car['leiras'] ?? '') ?></textarea><br>

            <label for="ar">Ár (Ft):</label>
            <input type="number" id="ar" name="ar" value="<?= htmlspecialchars($_POST['ar'] ?? $car['ar'] ?? '') ?>" required min="0" step="any"><br> <!-- step="any" a tizedesekhez, ha float -->

              <!-- Képek Kezelése Szekció -->
              <div class="image-management-container" style="margin-top: 20px; border: 1px solid #eee; padding: 15px;">
                    <h4>Képek kezelése</h4>

                    <?php
                        // Ha hiba történt a POST kérésben, a $car_images még a régi adatokat tartalmazza.
                        // Ideális esetben a POST feldolgozás utáni átirányítás miatt ez nem gond.
                        // Ha nincs átirányítás, akkor a $car_images-t is frissíteni kellene a POST blokkban
                        // a $updated_images alapján, hogy a felhasználó a módosítás utáni állapotot lássa.
                        // Mivel most átirányítást használunk (ajánlott), ez a $car_images a GET kérésből jön.
                        $images_to_display = $car_images ?? []; // Biztosítjuk, hogy tömb legyen
                     ?>

                    <?php if (!empty($images_to_display)): ?>
                        <p>Jelenlegi képek (Az első az alapértelmezett):</p>
                        <div class="image-list">
                            <?php foreach ($images_to_display as $index => $img_web_path): ?>
                                <div class="image-item">
                                    <img src="<?= htmlspecialchars($img_web_path) ?>" alt="Jármű kép <?= $index + 1 ?>" loading="lazy">
                                    <div class="controls">
                                        <?php if ($index === 0): ?>
                                             <span class="primary-indicator d-block mb-1">(Elsődleges)</span>
                                        <?php endif; ?>
                                        <label>
                                            <input type="radio" name="primary_image" value="<?= htmlspecialchars($img_web_path) ?>" <?= ($index === 0) ? 'checked' : '' ?>>
                                            Elsődleges
                                        </label>
                                        <label>
                                            <input type="checkbox" name="delete_image[]" value="<?= htmlspecialchars($img_web_path) ?>">
                                            Törlés
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                    <?php else: ?>
                        <p class="text-muted">Nincsenek még képek feltöltve ehhez a járműhöz.</p>
                    <?php endif; ?>


                    <div style="margin-top: 15px;">
                        <label for="new_images" style="display: block; margin-bottom: 5px;">Új képek hozzáadása (többet is kiválaszthatsz):</label>
                        <input type="file" id="new_images" name="new_images[]" accept="image/jpeg, image/png, image/gif, image/webp" multiple style="display: block; width: 100%;">
                        <small style="display: block; margin-top: 5px;">Engedélyezett: JPG, PNG, GIF, WEBP. Max 5MB/kép.</small>
                    </div>
                </div>
                <!-- Képek Kezelése Vége -->

            <button type="submit" name="update_vehicle">Mentés</button>
        </form>
        <?php else: ?>
             <div class="alert alert-warning" role="alert">
                 A jármű adatai nem érhetők el.
             </div>
        <?php endif; // vége if (isset($car)) ?>
    </div>
    <footer class="container mt-5 mb-3 text-center text-muted">
        R&J Admin - @ <?=date('Y') ?>
    </footer>

    <!-- Bootstrap JS (opcionális, ha használsz JS komponenseket) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Egyszerű menü toggle (ha a CSS-ed használ .active class-t a nav elemen)
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('header nav');
        if(menuToggle && nav) {
            menuToggle.addEventListener('click', () => {
                nav.classList.toggle('active');
            });
        }

        // Alapvető kliens oldali validáció jelzése (Bootstrap stílusokhoz)
        // Ez csak vizuális visszajelzést ad, a szerver oldali validáció a fontos!
        const form = document.getElementById('modositas-form');
        if(form) {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    // A required mezők miatt a böngésző megállítja a küldést
                    // Itt lehetne további egyedi ellenőrzéseket végezni és hibaüzeneteket megjeleníteni
                }
                // Bootstrap validációs class hozzáadása a vizuális visszajelzéshez
                form.classList.add('was-validated');
            }, false);
        }
    </script>

</body>
</html>