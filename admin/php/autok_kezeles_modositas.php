<?php
// Hibajelentés bekapcsolása fejlesztéshez (éles környezetben kapcsold ki)
error_reporting(E_ALL); // Fejlesztéshez érdemes bekapcsolni
ini_set('display_errors', 1); // Fejlesztéshez érdemes bekapcsolni

include "./db_connection.php"; // Feltételezem, hogy ez $db néven hozza létre a kapcsolatot
include "./adatLekeres.php"; // Feltételezem, hogy ez tartalmazza az adatokLekerese funkciót

session_start();

// --- Modal include helyett közvetlen üzenetkezelés ---
function set_alert($message, $type = 'info') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
}

function display_alert() {
    if (isset($_SESSION['alert_message'])) {
        $message = $_SESSION['alert_message'];
        $type = $_SESSION['alert_type'] ?? 'info';
        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($message) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
    }
}
// --- Üzenetkezelés vége ---


// Ellenőrizzük, hogy a $db objektum létezik-e (a db_connection.php-ból)
if (!isset($db) || $db->connect_error) {
    // Adjunk informatívabb hibaüzenetet
    $error_msg = isset($db) ? $db->connect_error : 'A $db kapcsolat objektum nem jött létre.';
    die("Adatbázis kapcsolati hiba: " . $error_msg);
}

$kocsiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kocsiId <= 0) {
    set_alert("Érvénytelen jármű ID.", "danger");
    header("Location: autok_kezeles.php"); // Visszairányítás, ha nincs ID
    exit;
}

// --- Jármű Adatainak Lekérdezése ---
$stmt_car = $db->prepare("SELECT * FROM jarmuvek WHERE jarmu_id = ?");
// Hibaellenőrzés prepare után
if ($stmt_car === false) {
    die("Hiba az SQL előkészítésekor (jármű lekérdezés): " . $db->error);
}
$stmt_car->bind_param("i", $kocsiId);
if (!$stmt_car->execute()) {
     die("Hiba az SQL végrehajtásakor (jármű lekérdezés): " . $stmt_car->error);
}
$result_car = $stmt_car->get_result();

if ($result_car->num_rows > 0) {
    $car = $result_car->fetch_assoc();
    // Képek JSON dekódolása PHP tömbbé
    $car_images = json_decode($car['kep_url'] ?? '[]', true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Hibakezelés, ha a JSON érvénytelen
        $car_images = [];
        // Adjunk erről is jelzést, de ne álljon le a script emiatt feltétlenül
        set_alert("Figyelmeztetés: Hiba a jármű képeinek betöltésekor (érvénytelen JSON a 'kep_url'-ben: " . json_last_error_msg() . ").", "warning");
    }
    // Biztosítjuk, hogy tömb legyen
    if (!is_array($car_images)) {
        $car_images = [];
    }

} else {
    set_alert("A keresett jármű (ID: " . $kocsiId . ") nem található.", "danger");
    header("Location: autok_kezeles.php"); // Visszairányítás, ha nincs ilyen autó
    exit;
}
$stmt_car->close();


// --- Jármű Módosítása (POST kérés feldolgozása) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vehicle'])) {
    $jarmu_id = $_POST['jarmu_id'] ? (int)$_POST['jarmu_id'] : 0;

    if ($jarmu_id !== $kocsiId) {
        set_alert("Biztonsági hiba: Azonosító eltérés.", "danger");
        header("Location: autok_kezeles.php");
        exit;
    }

    // Alapadatok kinyerése
    $felhasznalas_id = $_POST['felhasznalas_id'];
    $szerviz_id = $_POST['szerviz_id'];
    $gyarto = trim($_POST['gyarto']);
    $tipus = trim($_POST['tipus']);
    $motor = trim($_POST['motor']);
    $gyartasi_ev = $_POST['gyartasi_ev'];
    $leiras = trim($_POST['leiras']);
    $ar = $_POST['ar'];

    // --- Képkezelés ---
    $current_images = $car_images; // A DB-ből betöltött (ideális esetben már /php/kepek/... formátumú) képek listája
    $updated_images = []; // Az új képlista inicializálása
    $upload_errors = [];

    // === HELYES ÚTVONALAK DEFINIÁLÁSA ===
    // A képek FIZIKAI tárolási helye a szerveren
    $image_folder_physical = $_SERVER['DOCUMENT_ROOT'] . '/berles/kepek/';
    // A képek WEBEN elérhető útvonala (ezt tároljuk a DB-ben és használjuk src-ben)
    $image_folder_web_base = '/berles/kepek/';
    // ====================================


    // 1. Képek törlése
    $images_to_delete = isset($_POST['delete_image']) ? $_POST['delete_image'] : [];
    if (!is_array($images_to_delete)) $images_to_delete = [];

    foreach ($current_images as $img_web_path) { // $img_web_path pl. /php/kepek/kep.jpg
        if (!in_array($img_web_path, $images_to_delete)) {
            // Ha nincs a törlendők között, megtartjuk (a webes útvonalat)
            $updated_images[] = $img_web_path;
        } else {
            // Ha törölni kell:
            // Képezzük a FIZIKAI útvonalat a törléshez
            $physical_path_to_delete = $_SERVER['DOCUMENT_ROOT'] . $img_web_path;
            // Töröljük a fizikai fájlt (ha létezik)
            if (file_exists($physical_path_to_delete)) {
                if (!@unlink($physical_path_to_delete)) {
                    $upload_errors[] = "Nem sikerült törölni a fájlt: " . htmlspecialchars($physical_path_to_delete);
                }
            } else {
                 // Opcionális: jelezhetjük, ha a törlendő fájl nem is létezett
                 // $upload_errors[] = "Figyelmeztetés: Törölni kívánt fájl nem található: " . htmlspecialchars($physical_path_to_delete);
            }
        }
    }

    // 2. Új képek feltöltése
    if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB limit

        // Ellenőrizzük, hogy a célmappa létezik és írható-e
         if (!is_dir($image_folder_physical)) {
             // Megpróbáljuk létrehozni rekurzívan
             // Fontos: A webszervernek írási joga kell legyen a $_SERVER['DOCUMENT_ROOT'] . '/php/' mappára!
             if (!@mkdir($image_folder_physical, 0775, true) && !is_dir($image_folder_physical)) {
                 $upload_errors[] = "Nem sikerült létrehozni a képek mappát: " . htmlspecialchars($image_folder_physical) . ". Ellenőrizze a jogosultságokat!";
                 // Ha a mappa nincs meg, nincs értelme továbbmenni a feltöltéssel erre a mappára
             }
         }

        // Csak akkor próbálunk feltölteni, ha a mappa létezik és írható
        if (is_dir($image_folder_physical) && is_writable($image_folder_physical)) {
            foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['new_images']['name'][$key];
                    $file_size = $_FILES['new_images']['size'][$key];
                    $file_type = $_FILES['new_images']['type'][$key];
                    $file_tmp = $_FILES['new_images']['tmp_name'][$key];

                    // Ellenőrzések
                    if (!in_array(strtolower(mime_content_type($file_tmp)), $allowed_types)) { // Biztonságosabb mime type ellenőrzés
                        $upload_errors[] = "Fájl ('".htmlspecialchars($file_name)."') típusa ('".htmlspecialchars(mime_content_type($file_tmp))."') nem engedélyezett.";
                        continue;
                    }
                    if ($file_size > $max_size) {
                        $upload_errors[] = "Fájl ('".htmlspecialchars($file_name)."') mérete túl nagy (max 5MB).";
                        continue;
                    }

                    // Egyedi fájlnév generálása
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $unique_filename = uniqid('car_'.$jarmu_id.'_', true) . '.' . strtolower($file_extension);

                    // Cél útvonalak
                    $destination_path_physical = $image_folder_physical . $unique_filename; // Fizikai mentés helye
                    $web_path_for_db = $image_folder_web_base . $unique_filename; // Adatbázisba mentendő webes útvonal

                    // Fájl áthelyezése
                    if (move_uploaded_file($file_tmp, $destination_path_physical)) {
                        // Hozzáadás az új listához (a HELYES WEB útvonallal)
                        $updated_images[] = $web_path_for_db;
                    } else {
                        $upload_errors[] = "Hiba történt a fájl ('".htmlspecialchars($file_name)."') áthelyezésekor ide: ".htmlspecialchars($destination_path_physical);
                         // További debug info lehet hasznos: error_get_last()
                    }
                } elseif ($_FILES['new_images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $upload_errors[] = "Feltöltési hiba a fájlnál ('".htmlspecialchars($_FILES['new_images']['name'][$key])."'): kód " . $_FILES['new_images']['error'][$key];
                }
            }
        } else {
             // Hiba, ha a mappa nem létezik vagy nem írható a feltöltés előtt
             if(!is_dir($image_folder_physical)) {
                $upload_errors[] = "A képek mappa nem létezik és nem sikerült létrehozni: " . htmlspecialchars($image_folder_physical);
             } else {
                $upload_errors[] = "A képek mappa nem írható: " . htmlspecialchars($image_folder_physical);
             }
        }
    }

     // 3. Elsődleges kép beállítása
    $primary_image = isset($_POST['primary_image']) ? $_POST['primary_image'] : null;
     // Fontos: $primary_image értéke a DB-ből jön, azaz /php/kepek/... formátumú kell legyen!
    if ($primary_image && in_array($primary_image, $updated_images)) {
        $primary_key = array_search($primary_image, $updated_images);
        if ($primary_key !== false && $primary_key !== 0) {
            $primary_item = array_splice($updated_images, $primary_key, 1)[0];
            array_unshift($updated_images, $primary_item);
        }
    }
    // Nincs szükség else ágra itt, ha nincs primary kiválasztva, a sorrend marad

    // --- Képkezelés VÉGE ---


    // Adatbázis frissítése
    // Az $updated_images már a helyes, webes (/php/kepek/...) útvonalakat tartalmazza
    $new_kep_url_json = json_encode(array_values($updated_images));

    $modositas = $db->prepare("UPDATE jarmuvek SET
                                felhasznalas_id = ?, szerviz_id = ?, gyarto = ?,
                                tipus = ?, motor = ?, gyartasi_ev = ?,
                                leiras = ?, ar = ?, kep_url = ?
                              WHERE jarmu_id = ?");

    if ($modositas === false) {
        set_alert("Hiba az SQL utasítás előkészítésekor (jármű frissítés): " . $db->error, "danger");
    } else {
        $modositas->bind_param("iisssssisi",
                                $felhasznalas_id, $szerviz_id, $gyarto,
                                $tipus, $motor, $gyartasi_ev,
                                $leiras, $ar, $new_kep_url_json,
                                $jarmu_id);

        if ($modositas->execute()) {
            // Siker esetén az alap üzenet
             $alert_msg = "Sikeres módosítás!";
             $alert_type = "success";

            // Ha voltak feltöltési hibák, jelezzük és módosítjuk az üzenet típusát
            if (!empty($upload_errors)) {
                 $alert_msg .= " Figyelem, képkezelési hibák történtek: " . implode('; ', $upload_errors);
                 $alert_type = 'warning';
            }
             set_alert($alert_msg, $alert_type);

            // Frissítjük az oldalon megjelenő adatokat is
            $car['felhasznalas_id'] = $felhasznalas_id;
            $car['szerviz_id'] = $szerviz_id;
            $car['gyarto'] = $gyarto;
            $car['tipus'] = $tipus;
            $car['motor'] = $motor;
            $car['gyartasi_ev'] = $gyartasi_ev;
            $car['leiras'] = $leiras;
            $car['ar'] = $ar;
            $car['kep_url'] = $new_kep_url_json; // JSON string
            $car_images = $updated_images; // PHP tömb

        } else {
            set_alert("Hiba a módosítás során (jármű frissítés): " . $modositas->error, "danger");
        }
        $modositas->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modositas</title>
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
    <h1>Módosítás</h1>
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

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit">Vissza a járművekhez</button></a>
    </div>

    <div id="jarmuvek_modositas" class="tartalmi-resz">


        <form method="POST" enctype="multipart/form-data" class="form" id="modositas-form">

            <input type="hidden" name="jarmu_id" value="<?= htmlspecialchars($car['jarmu_id']) ?>">

            <label for="gyarto">Gyártó:</label>
            <input type="text" name="gyarto" value="<?= htmlspecialchars($car['gyarto']) ?>"><br>

            <label for="tipus">Típus:</label>
            <input type="text" name="tipus" value="<?= htmlspecialchars($car['tipus']) ?>"><br>

            <label for="motor">Motor:</label>
            <input type="text" name="motor" value="<?= htmlspecialchars($car['motor']) ?>"><br>

            <label for="felhasznalas_id">Felhasználási mód:</label>
            <select name="felhasznalas_id">
                <option value="<?= htmlspecialchars($car['felhasznalas_id']) ?>">-- Kérem válassz egyet --</option>
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
            <input type="number" name="szerviz_id" value="<?= htmlspecialchars($car['szerviz_id']) ?>"><br>

            <label for="gyartasi_ev">Gyártási év:</label>
            <input type="date" name="gyartasi_ev" value="<?= htmlspecialchars($car['gyartasi_ev']) ?>"><br>

            <label for="leiras">Leírás:</label>
            <textarea name="leiras"><?= htmlspecialchars($car['leiras']) ?></textarea><br>

            <label for="ar">Ár:</label>
            <input type="number" name="ar" value="<?= htmlspecialchars($car['ar']) ?>"><br>

              <!-- Képek Kezelése Szekció -->
              <div class="image-management-container mb-4">
                    <h4>Képek kezelése</h4>

                    <?php if (!empty($car_images)): ?>
                        <p>Jelenlegi képek:</p>
                        <div class="image-list mb-3">
                            <?php foreach ($car_images as $index => $img_web_path): // Most már /php/kepek/... útvonal van itt ?>
                                <div class="image-item">
                                     <!-- Az src attribútum a webes útvonalat használja -->
                                    <img src="<?= htmlspecialchars($img_web_path) ?>" alt="Jármű kép <?= $index + 1 ?>">
                                    <div class="controls">
                                        <?php if ($index === 0): ?>
                                             <span class="primary-indicator d-block mb-1">(Elsődleges)</span>
                                        <?php endif; ?>
                                        <label>
                                             <!-- A value is a webes útvonalat tartalmazza -->
                                            <input type="radio" name="primary_image" value="<?= htmlspecialchars($img_web_path) ?>" <?= ($index === 0) ? 'checked' : '' ?>>
                                            Elsődleges
                                        </label>
                                        <label>
                                             <!-- A value is a webes útvonalat tartalmazza -->
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


                    <div class="mb-3">
                        <label for="new_images" class="form-label">Új képek hozzáadása (többet is kiválaszthatsz):</label>
                        <input type="file" class="form-control" id="new_images" name="new_images[]" accept="image/jpeg, image/png, image/gif, image/webp" multiple>
                        <div class="form-text">Engedélyezett formátumok: JPG, PNG, GIF, WEBP. Max méret: 5MB / kép.</div>
                    </div>
                </div>
                <!-- Képek Kezelése Vége -->

            <button type="submit" name="update_vehicle">Mentés</button>
        </form>
    </div>
</body>
</html>