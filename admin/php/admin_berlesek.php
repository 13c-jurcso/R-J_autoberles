<?php
session_start(); // Session indítása az üzenetekhez

// Adatbázis kapcsolat és segédfüggvények
include "./db_connection.php";
include "./adatLekeres.php";
// Az adatLekeres.php-t most nem használjuk itt, de a db_connection.php-ra szükség van

// Admin ellenőrzés
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == false) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: ../../php/index.php");
    exit();
}

// Ellenőrizzük a DB kapcsolatot
if (!isset($db) || $db->connect_error) {
    $error_msg = isset($db) ? $db->connect_error : 'A $db kapcsolat objektum nem jött létre.';
    // Naplózás és session üzenet a felhasználónak
    error_log("Adatbázis kapcsolati hiba (admin_berlesek.php): " . $error_msg);
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Adatbázis kapcsolati hiba. Kérjük, próbálja meg később.</div>';
    // Nem szakítjuk meg a script futását itt, hogy a HTML váza megjelenhessen a hibaüzenettel
}

// --- Segédfüggvények (az api.php-ból átemelve) ---
function sanitize_input($data)
{
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    // Csak trim és alap htmlspecialchars, mivel a felhasználónév lehet speciálisabb
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validate_date($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// --- POST Kérések Kezelése (Hozzáadás és Törlés) ---

// Bérlés Hozzáadása
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_berles']) && !isset($db->connect_error)) {
    // Adatok fogadása és tisztítása
    $jarmu_id = filter_input(INPUT_POST, 'jarmu_id', FILTER_VALIDATE_INT);
    $felhasznalo = sanitize_input($_POST['felhasznalo'] ?? ''); // FelhasználóNÉV fogadása
    $tol = sanitize_input($_POST['tol'] ?? '');
    $ig = sanitize_input($_POST['ig'] ?? '');

    // Validáció
    $errors = [];
    if (!$jarmu_id || $jarmu_id <= 0) $errors[] = "Érvénytelen jármű ID.";
    if (empty($felhasznalo)) $errors[] = "A felhasználónév megadása kötelező.";

    // Ellenőrzés: Létezik-e a felhasználónév?
    $checkUserStmt = $db->prepare("SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_nev = ?");
    if ($checkUserStmt) {
        $checkUserStmt->bind_param("s", $felhasznalo);
        $checkUserStmt->execute();
        $checkUserStmt->bind_result($userCount);
        $checkUserStmt->fetch();
        $checkUserStmt->close();
        if ($userCount == 0) $errors[] = "A megadott felhasználónév ('" . htmlspecialchars($felhasznalo) . "') nem létezik.";
    } else {
        $errors[] = "Hiba a felhasználónév ellenőrzésekor.";
        error_log("SQL Prepare hiba (felhasználó ellenőrzés): " . $db->error);
    }

    // Ellenőrzés: Létezik-e a jármű ID?
    $checkCarStmt = $db->prepare("SELECT COUNT(*) FROM jarmuvek WHERE jarmu_id = ?");
    if ($checkCarStmt) {
        $checkCarStmt->bind_param("i", $jarmu_id);
        $checkCarStmt->execute();
        $checkCarStmt->bind_result($carCount);
        $checkCarStmt->fetch();
        $checkCarStmt->close();
        if ($carCount == 0) $errors[] = "A megadott jármű ID ('" . htmlspecialchars($jarmu_id) . "') nem létezik.";
    } else {
        $errors[] = "Hiba a jármű ID ellenőrzésekor.";
        error_log("SQL Prepare hiba (jármű ellenőrzés): " . $db->error);
    }


    if (!validate_date($tol)) $errors[] = "Érvénytelen átvételi dátum formátum (YYYY-MM-DD).";
    if (!validate_date($ig)) $errors[] = "Érvénytelen leadási dátum formátum (YYYY-MM-DD).";
    if (validate_date($tol) && validate_date($ig) && strtotime($ig) < strtotime($tol)) {
        $errors[] = "A leadás dátuma nem lehet korábbi az átvétel dátumánál.";
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">A leadás dátuma nem lehet korábbi az átvétel dátumánál.</div>';
    }
    // Itt lehetne további validáció: ütközik-e a bérlés más bérléssel ugyanarra az autóra?

    if (!empty($errors)) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a hozzáadás során:<ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
    } else {
        // Prepared Statement
        $stmt = $db->prepare("INSERT INTO berlesek (jarmu_id, felhasznalo, tol, ig) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az SQL előkészítésekor (INSERT).</div>';
            error_log("SQL Prepare Hiba (INSERT berlesek): " . $db->error);
        } else {
            // jarmu_id (i), felhasznalo (s), tol (s), ig (s) -> isss
            $stmt->bind_param("isss", $jarmu_id, $felhasznalo, $tol, $ig);
            if ($stmt->execute()) {
                $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Bérlés sikeresen hozzáadva!</div>';
            } else {
                $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bérlés hozzáadásakor: ' . htmlspecialchars($stmt->error) . '</div>';
                error_log("SQL Execute Hiba (INSERT berlesek): " . $stmt->error);
            }
            $stmt->close();
        }
    }
    // Átirányítás PRG minta szerint
    header("Location: ./admin_berlesek.php");
    exit;
}

// Bérlés Törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_berles']) && !isset($db->connect_error)) {
    $berles_id = filter_input(INPUT_POST, 'berles_id', FILTER_VALIDATE_INT);

    if (!$berles_id || $berles_id <= 0) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Érvénytelen bérlés ID a törléshez.</div>';
    } else {
        $stmt = $db->prepare("DELETE FROM berlesek WHERE berles_id = ?");
        if ($stmt === false) {
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az SQL előkészítésekor (DELETE).</div>';
            error_log("SQL Prepare Hiba (DELETE berlesek): " . $db->error);
        } else {
            $stmt->bind_param("i", $berles_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Bérlés (ID: ' . $berles_id . ') sikeresen törölve!</div>';
                } else {
                    $_SESSION['uzenet'] = '<div class="alert alert-warning" role="alert">A törlés nem sikerült (lehet, hogy a bérlés ID már nem létezett).</div>';
                }
            } else {
                $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bérlés törlésekor: ' . htmlspecialchars($stmt->error) . '</div>';
                error_log("SQL Execute Hiba (DELETE berlesek): " . $stmt->error);
            }
            $stmt->close();
        }
    }
    // Átirányítás PRG minta szerint
    header("Location: ./admin_berlesek.php");
    exit;
}


// --- Bérlések lekérdezése a táblázathoz ---
$berlesek = []; // Alapértelmezett üres tömb
$fetch_error = null; // Hibaüzenet a lekérdezéshez

// Csak akkor próbálkozunk lekérdezni, ha nincs adatbázis kapcsolati hiba
if (!isset($db->connect_error)) {
    $sql = "SELECT
                b.berles_id, b.tol, b.ig, b.kifizetve,
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
        // Nem hiba, ha nincs eredmény, csak üres a tömb
        $result->free();
    } else {
        $fetch_error = "Hiba a bérlések lekérdezése során: " . htmlspecialchars($db->error);
        error_log("SQL Hiba (bérlések lekérdezése): " . $db->error);
    }
} else {
    // Ha már a kapcsolatnál hiba volt, azt jelezzük
    $fetch_error = "Az adatbázis kapcsolat hibája miatt a bérlések nem tölthetők be.";
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

    <h1>Bérlések Kezelése</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek">Járművek </button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag">Jogosultságok</button></a>
        <a href="./admin_berlesek.php"><button type="submit" id="berlesek">Bérlések</button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
    <hr>

    <!-- Üzenetek helye -->
    <div id="uzenet-container">
        <?php
        if (isset($_SESSION['uzenet'])) {
            echo $_SESSION['uzenet'];
            unset($_SESSION['uzenet']); // Üzenet törlése a megjelenítés után
        }
        // A fetch_error most már csak lekérdezési hibát jelez, a kapcsolati hibát a session üzenet kezeli fentebb
        if ($fetch_error && !isset($db->connect_error)) {
            echo '<div class="alert alert-warning" role="alert">' . $fetch_error . '</div>';
        }
        ?>
    </div>

    <h2>Új bérlés hozzáadása</h2>
    <?php if (isset($db->connect_error)): ?>
        <div class="alert alert-danger">Az adatbázis kapcsolat hibás, új bérlés nem adható hozzá.</div>
    <?php else: ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="form needs-validation" novalidate>

            <label for="jarmu_id" class="form-label">Jármű:</label>
            <select name="jarmu_id" required>
                <?php
                $jarmuvek_sql = "SELECT jarmu_id, gyarto, tipus FROM jarmuvek";
                $jarmuvek = adatokLekerese($jarmuvek_sql);
                if (is_array($jarmuvek) && !empty($jarmuvek)) {
                    foreach ($jarmuvek as $j) {
                        echo '<option value="' . $j['jarmu_id'] . '">' . $j['gyarto'] . ' ' . $j['tipus'] . '</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }
                ?>
            </select>

            <label for="felhasznalo" class="form-label">Bérlő neve:</label>
            <select name="felhasznalo" id="felhasznalo" required>
                <option value="">-- Kérem válasszon --</option>
                <?php

                $user_sql = "SELECT felhasznalo_nev, nev FROM felhasznalo ORDER BY nev";
                $user = adatokLekerese($user_sql);
                if (is_array($user) && !empty($user)) {
                    foreach ($user as $u) {
                        echo '<option value="' . $u['felhasznalo_nev'] . '">' . $u['nev'] . ' (' . $u['felhasznalo_nev'] . ')</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }

                ?>
            </select>

            <label for="tol" class="form-label">Átvétel időpontja:</label>
            <input type="date" id="tol" name="tol" required>


            <label for="ig" class="form-label">Leadás dátuma:</label>
            <input type="date" id="ig" name="ig" required>


            <button type="submit" name="add_berles" class="btn btn-success">Hozzáadás</button>
        </form>
    <?php endif; ?>
    <hr>

    <h2>Aktuális Bérlések</h2>
    <div class="tartalmi-resz">
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gyártó</th>
                    <th>Típus</th>
                    <th>Bérlő Neve</th>
                    <th>Átvétel</th>
                    <th>Leadás</th>
                    <th>Kifizetés</th>
                    <th></th>
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
                                <?php
                                    // Ellenőrizzük a kifizetve értékét
                                    if ($berles['kifizetve'] == 0) {
                                        echo htmlspecialchars('helyszínen');
                                    } elseif ($berles['kifizetve'] == 1) {
                                        echo htmlspecialchars('utalással');
                                    } else {
                                        // Opcionális: Kezeld az egyéb (váratlan) értékeket, ha lehetnek
                                        echo htmlspecialchars($berles['kifizetve']); // Vagy pl. echo 'Ismeretlen';
                                    }
                                ?>
                            </td>
                            <td>
                                <input type="hidden" name="berles_id" value="<?php echo $berles['berles_id']; ?>">
                                <a href="./admin_berlesek_mod.php?id=<?= $berles['berles_id'] ?>"><button type="button" class="modositas_button">Módosítás</button></a>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm torles_button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmDeleteModal"
                                    data-berles-id="<?= htmlspecialchars($berles['berles_id']) ?>"
                                    title="Törlés">
                                    Törlés
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php elseif (!$fetch_error && !isset($db->connect_error)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Nincsenek aktuális bérlések.</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger">Hiba történt a bérlések betöltésekor vagy az adatbázis nem elérhető.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>

    <!-- Törlés Megerősítő Modal (Bootstrap) -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Törlés Megerősítése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Biztosan törölni szeretné ezt a bérlést? Ez a művelet nem vonható vissza.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtnActual">Törlés</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejtett Form a Törléshez -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="deleteForm" style="display: none;">
        <input type="hidden" name="berles_id" id="deleteBerlesId">
        <button type="submit" name="delete_berles" id="submitDeleteButton"></button>
    </form>


    <!-- Bootstrap Bundle JS -->
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

        // Bootstrap kliens oldali validáció inicializálása
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    // Dátum összehasonlítás (opcionális kliens oldali)
                    const tolInput = form.querySelector('#tol');
                    const igInput = form.querySelector('#ig');
                    if (tolInput && igInput && tolInput.value && igInput.value && igInput.value < tolInput.value) {
                        igInput.setCustomValidity('A leadás dátuma nem lehet korábbi az átvételnél.');
                        event.preventDefault();
                        event.stopPropagation();
                    } else if (igInput) {
                        igInput.setCustomValidity(''); // Hiba törlése
                    }

                    form.classList.add('was-validated')
                }, false);

                // Dátumhiba törlése gépeléskor
                const igInput = form.querySelector('#ig');
                if (igInput) {
                    igInput.addEventListener('input', () => igInput.setCustomValidity(''));
                }
            })
        })();

        // Törlés Modal Kezelése
        document.addEventListener('DOMContentLoaded', function() {
            let selectedBerlesId = null;
            const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal')); // Cache modal instance if needed elsewhere
            const deleteBerlesIdInput = document.getElementById('deleteBerlesId');
            const submitDeleteButton = document.getElementById('submitDeleteButton');

            // Minden törlés gomb eseménykezelése a táblázatban
            document.querySelectorAll('.torles_button').forEach(button => {
                button.addEventListener('click', function() {
                    selectedBerlesId = this.getAttribute('data-berles-id');
                    // A modal megjelenítése már a data-bs-toggle/target attribútumokkal történik
                });
            });

            // A modálon belüli "Törlés" gomb eseménykezelése
            const confirmDeleteBtnActual = document.getElementById('confirmDeleteBtnActual');
            if (confirmDeleteBtnActual) {
                confirmDeleteBtnActual.addEventListener('click', function() {
                    if (selectedBerlesId && deleteBerlesIdInput && submitDeleteButton) {
                        deleteBerlesIdInput.value = selectedBerlesId;
                        submitDeleteButton.click(); // A rejtett form elküldése
                    } else {
                        console.error("Hiba: Nem található a törlendő ID vagy a rejtett form elemei.");
                        // Esetleg egy hibaüzenet a felhasználónak
                    }
                    // Modal bezárása manuálisan, ha a form küldés nem irányít át azonnal
                    // confirmDeleteModal.hide(); // Erre általában nincs szükség a form submit miatt
                });
            }

            // Opcionális: Modal elrejtésekor töröljük a kiválasztott ID-t
            const modalElement = document.getElementById('confirmDeleteModal');
            if (modalElement) {
                modalElement.addEventListener('hidden.bs.modal', function() {
                    selectedBerlesId = null;
                    if (deleteBerlesIdInput) {
                        deleteBerlesIdInput.value = ''; // Ürítjük a rejtett inputot is
                    }
                });
            }
        });
    </script>

</body>

</html>