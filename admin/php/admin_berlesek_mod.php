<?php
session_start();

include "./db_connection.php"; // $db kapcsolatot hoz létre
include "./adatLekeres.php"; // adatokLekerese() függvényhez, ha használjuk a dropdownokhoz

// Admin ellenőrzés
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == false) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: ../../php/index.php");
    exit();
}

// Adatbázis kapcsolat ellenőrzése
if (!isset($db) || $db->connect_error) {
    $error_msg = isset($db) ? $db->connect_error : 'A $db kapcsolat objektum nem jött létre.';
    error_log("Adatbázis kapcsolati hiba (admin_berlesek_mod.php): " . $error_msg);
    // Súlyos hiba, itt megállunk, de session üzenetet hagyunk
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Adatbázis kapcsolati hiba. A módosítás nem lehetséges.</div>';
    header("Location: ./admin_berlesek.php"); // Vissza a listához
    exit;
}

// Bérlés ID lekérése a GET paraméterből és validálása
$berles_id_get = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($berles_id_get === false || $berles_id_get <= 0) {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Érvénytelen bérlés azonosító.</div>';
    header("Location: ./admin_berlesek.php"); // Visszairányítás a listához
    exit;
}

// --- Aktuális Bérlés Adatainak Lekérdezése ---
$stmt_berles = $db->prepare("SELECT * FROM berlesek WHERE berles_id = ?");
if ($stmt_berles === false) {
    error_log("SQL prepare hiba (bérlés lekérdezés): " . $db->error);
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a bérlés adatainak előkészítésekor.</div>';
    header("Location: ./admin_berlesek.php");
    exit;
}
$stmt_berles->bind_param("i", $berles_id_get);

if (!$stmt_berles->execute()) {
    error_log("SQL execute hiba (bérlés lekérdezés): " . $stmt_berles->error);
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba történt a bérlés adatainak lekérésekor.</div>';
    header("Location: ./admin_berlesek.php");
    exit;
}
$result_berles = $stmt_berles->get_result();

if ($result_berles->num_rows > 0) {
    $berles = $result_berles->fetch_assoc();
} else {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">A keresett bérlés (ID: ' . htmlspecialchars($berles_id_get) . ') nem található.</div>';
    header("Location: ./admin_berlesek.php");
    exit;
}
$stmt_berles->close();

// --- Járművek és Felhasználók Lekérdezése a Dropdownokhoz ---
$jarmuvek_lista = [];
$felhasznalok_lista = [];
$dropdown_error = null;

// Járművek
$jarmuvek_sql = "SELECT jarmu_id, gyarto, tipus FROM jarmuvek ORDER BY gyarto, tipus";
$jarmuvek_result = $db->query($jarmuvek_sql);
if ($jarmuvek_result) {
    while ($row = $jarmuvek_result->fetch_assoc()) {
        $jarmuvek_lista[] = $row;
    }
    $jarmuvek_result->free();
} else {
    $dropdown_error = "Hiba a járművek lekérdezésekor: " . $db->error;
    error_log($dropdown_error);
}

// Felhasználók
$felhasznalok_sql = "SELECT felhasznalo_nev, nev FROM felhasznalo ORDER BY nev";
$felhasznalok_result = $db->query($felhasznalok_sql);
if ($felhasznalok_result) {
    while ($row = $felhasznalok_result->fetch_assoc()) {
        $felhasznalok_lista[] = $row;
    }
    $felhasznalok_result->free();
} else {
    $dropdown_error = ($dropdown_error ? $dropdown_error . '; ' : '') . "Hiba a felhasználók lekérdezésekor: " . $db->error;
    error_log("Hiba a felhasználók lekérdezésekor: " . $db->error);
}


// --- Bérlés Módosítása (POST kérés feldolgozása) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_berles'])) {
    $berles_id_post = filter_input(INPUT_POST, 'berles_id', FILTER_VALIDATE_INT);

    // Alapvető ellenőrzés: a rejtett mezőben lévő ID egyezik a GET paraméterrel?
    if ($berles_id_post !== $berles_id_get) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Biztonsági hiba: Azonosító eltérés.</div>';
        error_log("Bérlés módosítási hiba: Azonosító eltérés. GET ID: " . $berles_id_get . ", POST ID: " . $berles_id_post);
        header("Location: ./admin_berlesek.php");
        exit;
    }

    // Adatok kinyerése és alapvető tisztítása/validálása
    $jarmu_id = filter_input(INPUT_POST, 'jarmu_id', FILTER_VALIDATE_INT);
    // Felhasználónév: engedjük a speciális karaktereket, csak trimmeljük
    $felhasznalo = trim($_POST['felhasznalo'] ?? '');
    $tol = trim($_POST['tol'] ?? '');
    $ig = trim($_POST['ig'] ?? '');
    // Kifizetve: csak 0 vagy 1 lehet érvényes
    $kifizetve_input = filter_input(INPUT_POST, 'kifizetve', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]);
    // Ha a validáció sikertelen (null) vagy a bemenet nem volt szám, default 0 (vagy a $berles eredeti értéke)
    $kifizetve = ($kifizetve_input === null || $kifizetve_input === false) ? $berles['kifizetve'] : $kifizetve_input; // Vagy adjunk hibát


    // --- Segédfüggvény dátum validáláshoz ---
    function validate_date_format($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    // Validáció
    $errors = [];
    if (!$jarmu_id || $jarmu_id <= 0) {
        $errors[] = "Érvénytelen jármű ID lett kiválasztva.";
    }
    if (empty($felhasznalo)) {
        $errors[] = "A bérlő (felhasználónév) kiválasztása kötelező.";
    }
    // Ellenőrzés: Létezik-e a kiválasztott felhasználónév? (Fontos, ha a lista valamiért hibás)
    $userExists = false;
    foreach ($felhasznalok_lista as $user) {
        if ($user['felhasznalo_nev'] === $felhasznalo) {
            $userExists = true;
            break;
        }
    }
    if (!$userExists && !empty($felhasznalo)) { // Csak akkor hiba, ha nem üres ÉS nem található
        $errors[] = "A kiválasztott felhasználónév ('" . htmlspecialchars($felhasznalo) . "') érvénytelen.";
    }

    // Ellenőrzés: Létezik-e a kiválasztott jármű ID?
    $carExists = false;
    foreach ($jarmuvek_lista as $car) {
        if ($car['jarmu_id'] == $jarmu_id) { // Figyeljünk az int összehasonlításra
            $carExists = true;
            break;
        }
    }
    if (!$carExists && $jarmu_id > 0) { // Csak akkor hiba, ha nem 0 ÉS nem található
        $errors[] = "A kiválasztott jármű ID ('" . htmlspecialchars($jarmu_id) . "') érvénytelen.";
    }


    if (!validate_date_format($tol)) {
        $errors[] = "Érvénytelen átvételi dátum formátum (YYYY-MM-DD).";
    }
    if (!validate_date_format($ig)) {
        $errors[] = "Érvénytelen leadási dátum formátum (YYYY-MM-DD).";
    }
    if (validate_date_format($tol) && validate_date_format($ig) && strtotime($ig) < strtotime($tol)) {
        $errors[] = "A leadás dátuma nem lehet korábbi az átvétel dátumánál.";
    }
    if ($kifizetve_input === null || $kifizetve_input === false) { // Ha a filter_input nem adott vissza 0-t vagy 1-et
        $errors[] = "Érvénytelen fizetési státusz lett kiválasztva.";
    }
    // Itt lehetne ütközésvizsgálat más bérlésekkel (kivéve saját magát)

    // Ha vannak validációs hibák
    if (!empty($errors)) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bevitt adatokban:<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $errors)) . '</li></ul></div>';
        // Az űrlap újratöltődik a POST adatokkal. Frissítjük a $berles tömböt, hogy a form a HIBÁS adatokat mutassa javításra.
        $berles['jarmu_id'] = $jarmu_id;
        $berles['felhasznalo'] = $felhasznalo;
        $berles['tol'] = $tol;
        $berles['ig'] = $ig;
        $berles['kifizetve'] = $kifizetve; // A validált vagy eredeti értékkel
    } else {
        // --- Adatbázis frissítése ---
        try {
            // SQL lekérdezés összeállítása
            $sql = "UPDATE berlesek SET jarmu_id = ?, felhasznalo = ?, tol = ?, ig = ?, kifizetve = ? WHERE berles_id = ?";
            $types = "isssii"; // jarmu_id(i), felhasznalo(s), tol(s), ig(s), kifizetve(i), berles_id(i)

            // Adatbázis kapcsolat ellenőrzése (ping)
            if (!$db->ping()) {
                error_log("Adatbázis kapcsolat hiba (ping failed) bérlés frissítése előtt: ID " . $berles_id_post);
                throw new Exception("Adatbázis kapcsolati hiba. Kérjük, próbálja újra.");
            }

            $modositas = $db->prepare($sql);
            if ($modositas === false) {
                error_log("SQL prepare hiba (bérlés frissítés): " . $db->error . " SQL: " . $sql);
                throw new Exception("Hiba történt a módosítás előkészítésekor.");
            }

            // Paraméterek kötése
            $modositas->bind_param($types, $jarmu_id, $felhasznalo, $tol, $ig, $kifizetve, $berles_id_post);

            if ($modositas->execute()) {
                // Sikeres módosítás esetén lekérdezzük az érintett sorok számát
                if ($modositas->affected_rows > 0) {
                    $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Bérlés (ID: ' . htmlspecialchars($berles_id_post) . ') sikeresen módosítva!</div>';
                } else {
                    // Lehet, hogy nem történt változás az adatokban
                    $_SESSION['uzenet'] = '<div class="alert alert-info" role="alert">A bérlés adatai nem változtak. (ID: ' . htmlspecialchars($berles_id_post) . ')</div>';
                }
                $modositas->close();

                // Átirányítás PRG mintával (önmagára, de GET paraméterrel)
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($berles_id_post) . "&status=ok");
                exit;
            } else {
                error_log("SQL execute hiba (bérlés frissítés): " . $modositas->error . " (ID: " . $berles_id_post . ")");
                throw new Exception("Hiba történt a módosítás során az adatbázisban. (" . htmlspecialchars($modositas->error) . ")");
            }
            // Close statement if execute failed but prepare was successful
            if ($modositas) $modositas->close(); // Biztonsági zárás, ha a throw előtt nem történt meg

        } catch (Exception $e) {
            // Hibakezelés a try blokkból
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">' . htmlspecialchars($e->getMessage()) . '</div>';
            // Hiba esetén is átirányítunk, hogy ne ragadjon be a POST, és a friss adatok töltődjenek be GET-re
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($berles_id_post) . "&status=error");
            exit;
        }
    } // Validációs hibák else ágának vége
} // POST kérés vége

// Friss üzenet kezelése a GET paraméter alapján (PRG után)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'ok' && !isset($_SESSION['uzenet'])) { // Csak ha nincs már konkrétabb üzenet
        // Itt nem feltétlen kell üzenet, mert a POST feldolgozó már betette
        // $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Módosítás sikeres!</div>';
    } elseif ($_GET['status'] == 'error' && !isset($_SESSION['uzenet'])) { // Csak ha nincs már konkrétabb üzenet
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Ismeretlen hiba történt a módosítás során.</div>';
    }
    // Töröljük a status paramétert az URL-ből, hogy ne maradjon ott refresh után (opcionális JS megoldás kellene ehhez)
}


?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bérlés Módosítása</title>
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

    <h1>Bérlés Módosítása (ID: <?= htmlspecialchars($berles['berles_id'] ?? $berles_id_get) ?>)</h1>
    <hr>

    <div id="uzenet-container">
        <!-- Üzenetek -->
        <?php
        if (isset($_SESSION['uzenet'])) {
            echo $_SESSION['uzenet'];
            unset($_SESSION['uzenet']);
        }
        if ($dropdown_error) {
            echo '<div class="alert alert-warning" role="alert">Hiba a legördülő listák adatainak betöltésekor: ' . htmlspecialchars($dropdown_error) . '</div>';
        }
        ?>
    </div>

    <div class="menu mb-3">
        <a href="./admin_berlesek.php"><button type="button" class="btn btn-secondary">Vissza a bérlésekhez</button></a>
    </div>

    <?php if (isset($berles)): // Csak akkor jelenítjük meg a formot, ha $berles létezik
    ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= urlencode($berles_id_get) ?>" class="form needs-validation" id="modositas-form" novalidate>

            <input type="hidden" name="berles_id" value="<?= htmlspecialchars($berles['berles_id'] ?? '') ?>">


            <label for="jarmu_id" class="form-label">Jármű:</label>
            <select id="jarmu_id" name="jarmu_id" class="form-select" required <?= empty($jarmuvek_lista) ? 'disabled' : '' ?>>
                <option value="">-- Kérem válasszon --</option>
                <?php if (!empty($jarmuvek_lista)): ?>
                    <?php foreach ($jarmuvek_lista as $j): ?>
                        <option value="<?= htmlspecialchars($j['jarmu_id']) ?>"
                            <?= (isset($berles['jarmu_id']) && $berles['jarmu_id'] == $j['jarmu_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['gyarto'] . ' ' . $j['tipus']) ?> (ID: <?= htmlspecialchars($j['jarmu_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nincsenek járművek az adatbázisban.</option>
                <?php endif; ?>
            </select>
            <div class="invalid-feedback">
                Kérjük, válasszon járművet.
            </div>


            <label for="felhasznalo" class="form-label">Bérlő (Felhasználónév):</label>
            <select id="felhasznalo" name="felhasznalo" class="form-select" required <?= empty($felhasznalok_lista) ? 'disabled' : '' ?>>
                <option value="">-- Kérem válasszon --</option>
                <?php if (!empty($felhasznalok_lista)): ?>
                    <?php foreach ($felhasznalok_lista as $f): ?>
                        <option value="<?= htmlspecialchars($f['felhasznalo_nev']) ?>"
                            <?= (isset($berles['felhasznalo']) && $berles['felhasznalo'] === $f['felhasznalo_nev']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['nev']) ?> (<?= htmlspecialchars($f['felhasznalo_nev']) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Nincsenek felhasználók az adatbázisban.</option>
                <?php endif; ?>
            </select>
            <div class="invalid-feedback">
                Kérjük, válasszon bérlőt.
            </div>


            <label for="tol" class="form-label">Átvétel időpontja:</label>
            <input type="date" id="tol" name="tol" class="form-control" value="<?= htmlspecialchars($berles['tol'] ?? '') ?>" required>
            <div class="invalid-feedback">
                Kérjük, adja meg az átvétel dátumát (ÉÉÉÉ-HH-NN).
            </div>


            <label for="ig" class="form-label">Leadás dátuma:</label>
            <input type="date" id="ig" name="ig" class="form-control" value="<?= htmlspecialchars($berles['ig'] ?? '') ?>" required>
            <div class="invalid-feedback">
                Kérjük, adja meg a leadás dátumát (ÉÉÉÉ-HH-NN). A leadás nem lehet korábbi az átvételnél.
            </div>


            <label for="kifizetve" class="form-label">Fizetési státusz:</label>
            <select id="kifizetve" name="kifizetve" class="form-select" required>
                <option value="">-- Kérem válasszon --</option>
                <option value="0" <?= (isset($berles['kifizetve']) && $berles['kifizetve'] == 0) ? 'selected' : '' ?>>Helyszínen</option>
                <option value="1" <?= (isset($berles['kifizetve']) && $berles['kifizetve'] == 1) ? 'selected' : '' ?>>Utalással</option>
                <!-- Esetleg más státuszok, ha vannak -->
            </select>
            <div class="invalid-feedback">
                Kérjük, válassza ki a fizetési státuszt.
            </div>
            <br>
            <button type="submit" name="update_berles" class="btn btn-primary">Módosítások Mentése</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            A bérlés adatai nem érhetők el vagy nem létezik. <a href="./admin_berlesek.php">Vissza a listához</a>.
        </div>
    <?php endif; // vége if ($berles)
    ?>


    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Egyszerű menü toggle (ha szükséges)
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
                    // Alap Bootstrap validáció
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    // Extra: Dátum összehasonlítás
                    const tolInput = form.querySelector('#tol');
                    const igInput = form.querySelector('#ig');
                    if (tolInput && igInput && tolInput.value && igInput.value) {
                        // Csak akkor hasonlítunk, ha mindkettő érvényes dátumnak tűnik (alap formátum)
                        if (new Date(igInput.value) < new Date(tolInput.value)) {
                            igInput.setCustomValidity('A leadás dátuma nem lehet korábbi az átvételnél.');
                            event.preventDefault(); // Megállítjuk a küldést
                            event.stopPropagation();
                        } else {
                            igInput.setCustomValidity(''); // Hiba törlése, ha rendben van
                        }
                    } else if (igInput) {
                        igInput.setCustomValidity(''); // Töröljük a hibát, ha valamelyik dátum érvénytelen volt eleve
                    }


                    form.classList.add('was-validated')
                }, false);

                // Dátumhiba törlése gépeléskor a 'leadás' mezőben
                const igInput = form.querySelector('#ig');
                if (igInput) {
                    igInput.addEventListener('input', () => {
                        // Ha a Bootstrap alap validáció érvényesnek ítéli, töröljük a custom hibát
                        if (igInput.validity.valid) {
                            igInput.setCustomValidity('');
                        }
                        // Az összehasonlítási hiba az 'submit' eseménykor kerül újra ellenőrzésre
                    });
                }
                // Hasonlóan a 'tól' mezőhöz is lehetne, ha az befolyásolja az 'ig' validitását
                const tolInput = form.querySelector('#tol');
                if (tolInput && igInput) {
                    tolInput.addEventListener('input', () => {
                        // Ha 'tól' változik, az 'ig' összehasonlítási hibáját törölhetjük,
                        // hogy a submit újra ellenőrizze.
                        if (igInput.validity.valid) { // Csak ha az 'ig' maga érvényes formátumú
                            igInput.setCustomValidity('');
                        }
                    });
                }

            })
        })()
    </script>

</body>

</html>