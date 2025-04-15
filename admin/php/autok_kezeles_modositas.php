<?php
session_start();

include "./db_connection.php"; // Feltételezem, hogy ez $db néven hozza létre a kapcsolatot
include "./adatLekeres.php"; // Feltételezem, hogy ez tartalmazza az adatokLekerese funkciót

if ($_SESSION['admin'] == false) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: ../../php/index.php");
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
    $jarmu_id_post = isset($_POST['jarmu_id']) ? (int)$_POST['jarmu_id'] : 0; // Másik nevet használunk, hogy ne ütközzön a $kocsiId-vel

    // Egyszerű CSRF védelem (ellenőrzi, hogy a formban lévő ID megegyezik-e a GET paraméterével)
    if ($jarmu_id_post !== $kocsiId) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Biztonsági hiba: Azonosító eltérés.</div>';
        error_log("CSRF gyanú vagy ID eltérés: GET ID=" . $kocsiId . ", POST ID=" . $jarmu_id_post);
        header("Location: autok_kezeles.php");
        exit;
    }

    // Alapadatok kinyerése és tisztítása
    $felhasznalas_id = filter_input(INPUT_POST, 'felhasznalas_id', FILTER_VALIDATE_INT);
    // Szerviz ID kezelése: lehet NULL vagy pozitív egész
    $szerviz_id_input = filter_input(INPUT_POST, 'szerviz_id', FILTER_SANITIZE_NUMBER_INT);
    $szerviz_id = (!empty($szerviz_id_input) && $szerviz_id_input > 0) ? (int)$szerviz_id_input : null; // NULL ha üres vagy érvénytelen

    $gyarto = trim(filter_input(INPUT_POST, 'gyarto', FILTER_SANITIZE_SPECIAL_CHARS));
    $tipus = trim(filter_input(INPUT_POST, 'tipus', FILTER_SANITIZE_SPECIAL_CHARS));
    $motor = trim(filter_input(INPUT_POST, 'motor', FILTER_SANITIZE_SPECIAL_CHARS));
    $gyartasi_ev_input = filter_input(INPUT_POST, 'gyartasi_ev'); // Dátum validálása később
    $leiras = trim(filter_input(INPUT_POST, 'leiras', FILTER_SANITIZE_SPECIAL_CHARS));
    $ar = filter_input(INPUT_POST, 'ar', FILTER_VALIDATE_FLOAT);

    // Alap validációk
    $errors = [];
    if (empty($gyarto)) $errors[] = "A gyártó megadása kötelező.";
    if (empty($tipus)) $errors[] = "A típus megadása kötelező.";
    if ($felhasznalas_id === false || $felhasznalas_id <= 0) $errors[] = "Érvénytelen felhasználási mód.";
    // Szerviz ID validáció (ha van rá szabály, pl. léteznie kell a szerviz táblában) - itt most csak azt nézzük, hogy szám-e vagy null
    // if ($szerviz_id !== null && $szerviz_id <= 0) $errors[] = "Érvénytelen Szerviz ID."; // Ha csak pozitív lehet
    if ($ar === false || $ar < 0) $errors[] = "Érvénytelen ár.";

    // Dátum validálása (YYYY-MM-DD formátum ellenőrzése)
    $gyartasi_ev = null; // Alapértelmezett érték
    if (!empty($gyartasi_ev_input)) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $gyartasi_ev_input)) {
            $errors[] = "Érvénytelen gyártási év formátum (YYYY-MM-DD szükséges).";
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $gyartasi_ev_input);
            if (!$d || $d->format('Y-m-d') !== $gyartasi_ev_input) {
                $errors[] = "Érvénytelen dátum a gyártási év mezőben.";
            } else {
                $gyartasi_ev = $gyartasi_ev_input; // Sikeres validálás után
            }
        }
    }

    // Ha vannak validációs hibák az alap adatokkal, ne folytassuk
    if (!empty($errors)) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bevitt adatokban:<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $errors)) . '</li></ul></div>';
        // Nem irányítunk át, az űrlap újratöltődik a POST adatokkal (lásd HTML value attribútumok)
    } else {
        // --- Képkezelés (csak ha nincsenek alap adat hibák) ---
        $current_images_from_db = $car_images; // A DB-ből betöltött képek listája (már tömb)
        $updated_images = []; // Az új képlista inicializálása
        $upload_errors = [];

        // Fontos: A fizikai elérési út a szerveren
        $image_folder_physical = $_SERVER['DOCUMENT_ROOT'] . '/R-J_autoberles/kepek/';
        // Fontos: A webes elérési út (amit a böngésző használ és a DB-be mentünk)
        $image_folder_web_base = '/R-J_autoberles/kepek/';

        // 1. Képek törlése
        $images_to_delete = isset($_POST['delete_image']) ? $_POST['delete_image'] : [];
        if (!is_array($images_to_delete)) $images_to_delete = []; // Biztosítjuk, hogy tömb legyen

        // Létrehozzuk az $updated_images listát a NEM törlendő képekből
        foreach ($current_images_from_db as $img_web_path) {
            if (!in_array($img_web_path, $images_to_delete)) {
                $updated_images[] = $img_web_path; // Megtartjuk a képet
            } else {
                // Törlésre jelölt kép - fizikailag is töröljük
                // Webes útból fizikai utat készítünk
                $physical_path_to_delete = $_SERVER['DOCUMENT_ROOT'] . $img_web_path;
                // Normalizáljuk az útvonalat (opcionális, de hasznos lehet Windows/Linux vegyes környezetben)
                $physical_path_to_delete = str_replace('/', DIRECTORY_SEPARATOR, $physical_path_to_delete);

                if (file_exists($physical_path_to_delete)) {
                    if (!@unlink($physical_path_to_delete)) {
                        $upload_errors[] = "Nem sikerült törölni a fájlt: " . htmlspecialchars(basename($img_web_path));
                        // Hiba esetén is kikerül a listából, de naplózzuk
                        error_log("Fájl törlési hiba: " . $physical_path_to_delete);
                    }
                } else {
                    // Ha a fájl már nem létezik, nem feltétlenül hiba, de lehet naplózni
                     error_log("Törlésre jelölt fájl nem található: " . $physical_path_to_delete);
                }
            }
        }

        // 2. Új képek feltöltése
        // Használjuk a 'new_images' nevet a file inputhoz az egyértelműség kedvéért
        if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name']) && !empty(array_filter($_FILES['new_images']['name']))) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            // Mappa ellenőrzése és létrehozása, ha szükséges
            if (!is_dir($image_folder_physical)) {
                if (!@mkdir($image_folder_physical, 0775, true) && !is_dir($image_folder_physical)) { // Rekurzív létrehozás
                    $upload_errors[] = "A képek tárolására szolgáló mappa ('" . htmlspecialchars($image_folder_physical) . "') létrehozása sikertelen. Ellenőrizze a jogosultságokat.";
                    error_log("Nem sikerült létrehozni a képek mappát: " . $image_folder_physical);
                }
            }

            // Csak akkor próbálunk feltölteni, ha a mappa létezik és írható
            if (is_dir($image_folder_physical) && is_writable($image_folder_physical)) {
                foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                    // Ellenőrizzük, hogy valóban történt-e feltöltés ehhez a kulcshoz
                    if ($_FILES['new_images']['error'][$key] === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                        $file_name = $_FILES['new_images']['name'][$key];
                        $file_size = $_FILES['new_images']['size'][$key];
                        // $file_tmp = $_FILES['new_images']['tmp_name'][$key]; // már megvan $tmp_name -ként

                        // MIME típus ellenőrzése biztonságosan
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $tmp_name);
                        finfo_close($finfo);

                        if (!in_array(strtolower($mime_type), $allowed_types)) {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Nem engedélyezett fájltípus (" . htmlspecialchars($mime_type) . ").";
                            continue; // Következő fájl
                        }
                        if ($file_size > $max_size) {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Túl nagy fájlméret (max 5MB).";
                            continue; // Következő fájl
                        }

                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        // Tisztított, egyedi fájlnév generálása
                        $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file_name, PATHINFO_FILENAME));
                        // Hozzáadjuk a jármű ID-t és egyedi időbélyeget a névhez
                        $unique_filename = 'car_' . $kocsiId . '_' . $safe_basename . '_' . time() . '_' . $key . '.' . $file_extension;

                        $destination_path_physical = $image_folder_physical . $unique_filename;
                        $web_path_for_db = $image_folder_web_base . $unique_filename; // Ezt mentjük a DB-be

                        if (move_uploaded_file($tmp_name, $destination_path_physical)) {
                            // Sikeres feltöltés után hozzáadjuk a webes utat az $updated_images tömbhöz
                            $updated_images[] = $web_path_for_db;
                        } else {
                            $upload_errors[] = htmlspecialchars($file_name) . ": Hiba a fájl áthelyezésekor a célmappába.";
                            error_log("move_uploaded_file hiba: " . $file_name . " -> " . $destination_path_physical . " (tmp: " . $tmp_name . ")");
                        }
                    } elseif ($_FILES['new_images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                        // Hiba történt a feltöltés során (de nem az, hogy nem volt fájl)
                        // https://www.php.net/manual/en/features.file-upload.errors.php
                        $upload_errors[] = "Feltöltési hiba történt (" . htmlspecialchars($_FILES['new_images']['name'][$key] ?? 'ismeretlen fájl') . "): kód " . $_FILES['new_images']['error'][$key];
                        error_log("Fájlfeltöltési hiba: " . ($_FILES['new_images']['name'][$key] ?? 'N/A') . ", Kód: " . $_FILES['new_images']['error'][$key]);
                    }
                }
            } else if (!isset($_SESSION['uzenet'])) { // Csak akkor jelezzük a mappa hibát, ha még nincs más hibaüzenet
                 if(!is_dir($image_folder_physical)) {
                     $upload_errors[] = "A képek célmappája ('".htmlspecialchars($image_folder_physical)."') nem létezik.";
                     error_log("Képek mappa nem létezik: " . $image_folder_physical);
                 } else {
                     $upload_errors[] = "A képek célmappája ('".htmlspecialchars($image_folder_physical)."') nem írható.";
                     error_log("Képek mappa nem írható: " . $image_folder_physical);
                 }
            }
        } // Új képek feltöltésének vége

        // 3. Elsődleges kép beállítása (sorrend módosítása)
        $primary_image_web_path = isset($_POST['primary_image']) ? $_POST['primary_image'] : null;

        // Csak akkor rendezünk át, ha van kiválasztott elsődleges kép ÉS az még létezik az $updated_images listában
        // (lehet, hogy törölték vagy nem is volt érvényes a kiválasztás)
        if ($primary_image_web_path && in_array($primary_image_web_path, $updated_images)) {
            $primary_key = array_search($primary_image_web_path, $updated_images);
            // Ha megtaláltuk ÉS nem ez az első elem már eleve
            if ($primary_key !== false && $primary_key > 0) {
                // Kivesszük az elemet a tömbből
                $primary_item = array_splice($updated_images, $primary_key, 1);
                // Betesszük a tömb elejére
                array_unshift($updated_images, $primary_item[0]);
            }
            // Ha $primary_key === 0, akkor már eleve az első, nincs teendő.
        } elseif (empty($updated_images) && $primary_image_web_path) {
             // Ha minden képet töröltek, de a primary_image mégis be van küldve (pl. böngésző cache miatt), ignoráljuk.
             // Nem hiba, csak nincs mit elsődlegesnek beállítani.
        } elseif (!empty($updated_images) && !$primary_image_web_path) {
             // Ha vannak képek, de nem választottak elsődlegest (pl. az előző elsődlegest törölték és nem választottak újat),
             // akkor az aktuálisan első kép (ami a törlések/feltöltések után az első lett) marad az elsődleges.
             // Nincs külön teendő, a tömb már a helyes sorrendet tükrözi.
        }

        // --- Képkezelés VÉGE ---


        // Adatbázis frissítése
        // Biztosítjuk, hogy a JSON tömb 0-tól indexelt legyen és érvényes legyen üres tömb esetén is ('[]')
        $new_kep_url_json = json_encode(array_values($updated_images));
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Ez kritikus hiba, valami nagyon elromlott a tömbbel
            error_log("JSON kódolási hiba a jarmu_id = " . $kocsiId . " frissítésekor. Adat: " . print_r($updated_images, true));
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Kritikus hiba történt a képlista mentésekor. A módosítás sikertelen.</div>';
            // Nem folytatjuk a DB frissítést
        } else {
            // Adatbázis kapcsolat újbóli ellenőrzése (lehet, hogy megszakadt közben)
             if ($db->ping()) {
                $modositas = $db->prepare("UPDATE jarmuvek SET
                                            felhasznalas_id = ?, szerviz_id = ?, gyarto = ?,
                                            tipus = ?, motor = ?, gyartasi_ev = ?,
                                            leiras = ?, ar = ?, kep_url = ?
                                          WHERE jarmu_id = ?");

                if ($modositas === false) {
                    error_log("SQL prepare hiba (jármű frissítés): " . $db->error);
                    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a módosítás előkészítésekor.</div>';
                } else {
                    // bind_param típusok: i=integer, d=double/float, s=string, b=blob
                    // szerviz_id most 'i' vagy 's' lehet, attól függően, hogyan kezeljük a NULL-t.
                    // Ha a $szerviz_id PHP null, 's'-ként NULL-t küld, 'i'-ként 0-t (általában). Legyen 's' a biztonság kedvéért, ha a DB oszlop NULLABLE.
                    // Ha a DB oszlop NOT NULL, akkor 'i' és a $szerviz_id nem lehet null. Itt most maradunk az 'i'-nél, feltételezve, hogy 0 vagy ID kerül bele.
                    // GYARTASI_EV is lehet NULL, 's' típussal biztonságosabb.
                    // AR is 'd'.
                    // KEP_URL is 's' (mivel JSON string).

                    // Módosított típus string: i (felh) i/s (szerv) s (gy) s (t) s (m) s (ev) s (leir) d (ar) s (kep) i (id)
                    // Használjunk 's'-t a nullable mezőknél (szerviz_id, gyartasi_ev) ha a DB engedi a NULL-t.
                    // Ha a szerviz_id NOT NULL, akkor marad 'i'. Most maradunk az eredeti 'i'-nél a szerviz_id-re.
                    // Gyartasi_ev legyen 's'.
                     $modositas->bind_param(
                        "iisssssdsi", // i, i, s, s, s, s, s, d, s, i
                        $felhasznalas_id,
                        $szerviz_id,   // PHP null itt 0-ként köthető 'i'-re, ami FK hiba lehet ha 0 nem valid ID. Ha nullable a DB oszlop, bind null kellene, amihez más technika kellhet.
                        $gyarto,
                        $tipus,
                        $motor,
                        $gyartasi_ev,  // NULL vagy 'YYYY-MM-DD' string
                        $leiras,
                        $ar,
                        $new_kep_url_json, // JSON string
                        $jarmu_id_post // A POST-ból jövő ID, ami már ellenőrzött $kocsiId-vel
                    );


                    if ($modositas->execute()) {
                        // Siker esetén az alap üzenet
                        $alert_msg = "Sikeres módosítás!";
                        $alert_type = "success";

                        // Ha voltak képkezelési hibák, jelezzük és módosítjuk az üzenet típusát
                        if (!empty($upload_errors)) {
                            $alert_msg .= " Figyelem, a következő képkezelési hibák történtek: <ul><li>" . implode('</li><li>', $upload_errors) . "</li></ul>";
                            $alert_type = 'warning'; // Típus módosítása warningra
                        }

                        $_SESSION['uzenet'] = '<div class="alert alert-' . htmlspecialchars($alert_type) . '" role="alert">' . $alert_msg . '</div>'; // HTML maradhat, mert a $upload_errors escape-elt

                        // === Átirányítás PRG mintával ===
                        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $kocsiId . "&status=ok");
                        exit;

                    } else {
                        error_log("SQL execute hiba (jármű frissítés): " . $modositas->error . " (ID: " . $kocsiId . ")");
                        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a módosítás során az adatbázisban. Lehetséges ok: érvénytelen Szerviz ID. (' . htmlspecialchars($modositas->error) . ')</div>';
                    }
                    $modositas->close();
                }
            } else {
                 error_log("Adatbázis kapcsolat hiba (ping failed) a frissítés előtt. ID: " . $kocsiId);
                 $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Adatbázis kapcsolati hiba. Kérjük, próbálja újra.</div>';
            }
        } // JSON encode hiba else ág vége
    } // validációs hibák else ágának vége
} // POST kérés vége

// Ha POST kérés volt és hiba történt (nem volt átirányítás),
// frissítsük a $car és $car_images változókat a POST adatok alapján,
// hogy a felhasználó ne veszítse el a beírt értékeket és lássa a képkezelési kísérlet eredményét.
// DE: A PRG minta miatt ez az ág ritkán fut le (csak ha a validáció vagy a képkezelés előtti részben van hiba).
// Ha a DB mentés sikertelen, akkor is átirányítunk (vagy kellene), hogy ne lehessen újraküldeni.
// Azért itt hagyjuk, hátha a validációs hibák miatt kell újratölteni az űrlapot.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($errors)) {
    // Frissítsük a $car tömböt a POST adatokkal, hogy az űrlap visszatöltse őket
    $car['gyarto'] = $_POST['gyarto'] ?? $car['gyarto'];
    $car['tipus'] = $_POST['tipus'] ?? $car['tipus'];
    $car['motor'] = $_POST['motor'] ?? $car['motor'];
    $car['felhasznalas_id'] = $_POST['felhasznalas_id'] ?? $car['felhasznalas_id'];
    $car['szerviz_id'] = $_POST['szerviz_id'] ?? $car['szerviz_id']; // Vigyázat, ez lehet üres string
    $car['gyartasi_ev'] = $_POST['gyartasi_ev'] ?? $car['gyartasi_ev'];
    $car['leiras'] = $_POST['leiras'] ?? $car['leiras'];
    $car['ar'] = $_POST['ar'] ?? $car['ar'];
    // A képeket nem töltjük újra a POST-ból, mert azok a GET kérésből származó állapotot kell mutassák
    // a $car_images már a GET kérésből van feltöltve. Ha a POST képkezelése sikertelen volt,
    // az $upload_errors jelzi, de a megjelenített képek a DB eredeti állapotát mutatják.
}

?>


<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jármű Módosítása</title>
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
    <h1>Jármű Módosítása: <?= htmlspecialchars(trim(($car['gyarto'] ?? '') . ' ' . ($car['tipus'] ?? ''))) ?></h1>
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

        <?php if (isset($car)): // Csak akkor jelenítjük meg a formot, ha $car létezik 
        ?>
            <form method="POST" enctype="multipart/form-data" class="form" id="modositas-form" novalidate> <!-- novalidate a böngésző alapértelmezett hibaüzeneteinek kikapcsolásához, ha sajátot használsz -->

                <input type="hidden" name="jarmu_id" value="<?= htmlspecialchars($car['jarmu_id'] ?? '') ?>">

                <label for="gyarto">Gyártó:</label>
                <input type="text" id="gyarto" name="gyarto" value="<?= htmlspecialchars($_POST['gyarto'] ?? $car['gyarto'] ?? '') ?>" required><br> <!-- POST érték használata hiba esetén -->

                <label for="tipus">Típus:</label>
                <input type="text" id="tipus" name="tipus" value="<?= htmlspecialchars($_POST['tipus'] ?? $car['tipus'] ?? '') ?>" required><br>

                <label for="motor">Hengerűrtartalom és motor Üzem:</label>
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
                            echo '<option value="' . htmlspecialchars($f['felhasznalas_id']) . '" ' . $selected . '>' . htmlspecialchars($f['nev']) . '</option>';
                        }
                    } else {
                        // Hibakezelés, ha a lekérdezés nem tömböt ad vissza
                        echo '<option value="" disabled>Hiba a módok betöltésekor.</option>';
                        error_log("Hiba adatokLekerese (felhasznalas): " . print_r($felhasznalas_modok, true));
                    }
                    ?>
                </select><br>

                <label for="szerviz_id">Szerviz ID:</label>
                <input type="number" id="szerviz_id" name="szerviz_id" value="<?= htmlspecialchars($_POST['szerviz_id'] ?? $car['szerviz_id'] ?? '') ?>" min="1"><br> <!-- min="1" ha csak pozitív ID lehet -->

                <label for="gyartasi_ev">Gyártási év:</label>
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
                <textarea name="leiras" id="message" rows="4"><?= htmlspecialchars($_POST['leiras'] ?? $car['leiras'] ?? '') ?></textarea><br>

                <label for="ar">Bérleti díj /nap:</label>
                <input type="number" id="ar" name="ar" value="<?= htmlspecialchars($_POST['ar'] ?? $car['ar'] ?? '') ?>" required min="0" step="any"><br> <!-- step="any" a tizedesekhez, ha float -->

                 <!-- Képek Kezelése Szekció -->
                 <div class="image-management-container card mb-3">
                    <div class="card-header">
                        <h4>Képek kezelése</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // A $car_images már a lap tetején be lett töltve a DB-ből
                        $images_to_display = $car_images ?? []; // Biztosítjuk, hogy tömb legyen
                        ?>

                        <?php if (!empty($images_to_display)): ?>
                            <p>Jelenlegi képek (Az első az alapértelmezett):</p>
                            <div class="image-list mb-3">
                                <?php foreach ($images_to_display as $index => $img_web_path): ?>
                                    <?php
                                        // Ellenőrizzük a kép elérési útját a biztonság kedvéért
                                        $safe_img_path = filter_var($img_web_path, FILTER_SANITIZE_URL);
                                        if (empty($safe_img_path) || !str_starts_with($safe_img_path, '/R-J_autoberles/kepek/')) {
                                            // Hibás vagy nem várt útvonal, kihagyjuk
                                            error_log("Hibás kép útvonal a listában: " . $img_web_path . " (ID: " . $kocsiId . ")");
                                            continue;
                                        }
                                    ?>
                                    <div class="image-item">
                                        <img src="<?= htmlspecialchars($safe_img_path) ?>" alt="Jármű kép <?= $index + 1 ?>" loading="lazy">
                                        <div class="controls">
                                            <?php if ($index === 0): ?>
                                                <span class="primary-indicator d-block mb-1">(Elsődleges)</span>
                                            <?php endif; ?>
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="radio" name="primary_image" value="<?= htmlspecialchars($safe_img_path) ?>" <?= ($index === 0) ? 'checked' : '' ?>>
                                                Legyen elsődleges
                                            </label>
                                            <label class="form-check-label text-danger">
                                                <input class="form-check-input" type="checkbox" name="delete_image[]" value="<?= htmlspecialchars($safe_img_path) ?>">
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
                            <label for="new_images" class="form-label">Új képek hozzáadása (többet is kiválaszthat):</label>
                            <!-- FONTOS: A file input neve legyen 'new_images[]', ne 'kep_url[]' -->
                            <input type="file" class="form-control" id="new_images" name="new_images[]" accept="image/jpeg, image/png, image/gif, image/webp" multiple>
                            <div class="form-text">Engedélyezett formátumok: JPG, PNG, GIF, WEBP. Max méret: 5MB / kép.</div>
                        </div>
                    </div>
                </div>
                <!-- Képek Kezelése Vége -->

              

                <button type="submit" name="update_vehicle" class="btn btn-primary">Módosítások Mentése</button>
            </form>
            <?php else: ?>
            <div class="alert alert-warning" role="alert">
                A jármű adatai nem érhetők el vagy nem található a megadott ID-vel jármű.
            </div>
        <?php endif; // vége if ($car) ?>
    </div>
    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Egyszerű menü toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('header nav');
        if (menuToggle && nav) {
            menuToggle.addEventListener('click', () => {
                nav.classList.toggle('active'); // Feltételezi, hogy van .active class a CSS-ben
            });
        }

        // Bootstrap kliens oldali validáció engedélyezése
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