<?php
session_start();

include "./db_connection.php"; // $db kapcsolatot hoz létre
// Az adatLekeres.php itt valószínűleg nem szükséges, mert specifikus lekérdezést végzünk

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
    error_log("Adatbázis kapcsolati hiba (admin_jog_mod.php): " . $error_msg);
    die("Adatbázis kapcsolati hiba. Kérjük, próbálja meg később.");
}

// Felhasználónév lekérése a GET paraméterből
$felhasznalo_nev_get = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($felhasznalo_nev_get)) {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Érvénytelen felhasználói azonosító.</div>';
    header("Location: ./admin_jogosultsag.php"); // Visszairányítás a listához
    exit;
}

// --- Felhasználó Adatainak Lekérdezése ---
$stmt_user = $db->prepare("SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?");
if ($stmt_user === false) {
    error_log("SQL prepare hiba (felhasználó lekérdezés): " . $db->error);
    die("Hiba történt a művelet előkészítésekor.");
}
$stmt_user->bind_param("s", $felhasznalo_nev_get);

if (!$stmt_user->execute()) {
    error_log("SQL execute hiba (felhasználó lekérdezés): " . $stmt_user->error);
    die("Hiba történt az adatok lekérésekor.");
}
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">A keresett felhasználó (' . htmlspecialchars($felhasznalo_nev_get) . ') nem található.</div>';
    header("Location: ./admin_jogosultsag.php");
    exit;
}
$stmt_user->close();

// --- Felhasználó Módosítása (POST kérés feldolgozása) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $felhasznalo_nev_post = isset($_POST['felhasznalo_nev']) ? trim($_POST['felhasznalo_nev']) : '';

    // Alapvető ellenőrzés: a rejtett mezőben lévő név egyezik a GET paraméterrel?
    if ($felhasznalo_nev_post !== $felhasznalo_nev_get) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Biztonsági hiba: Azonosító eltérés.</div>';
        error_log("Felhasználó módosítási hiba: Azonosító eltérés. GET: " . $felhasznalo_nev_get . ", POST: " . $felhasznalo_nev_post);
        header("Location: ./admin_jogosultsag.php");
        exit;
    }

    // Adatok kinyerése és tisztítása
    $nev = trim(filter_input(INPUT_POST, 'nev', FILTER_SANITIZE_SPECIAL_CHARS));
    $emailcim = trim(filter_input(INPUT_POST, 'emailcim', FILTER_SANITIZE_EMAIL));
    $szamlazasi_cim = trim(filter_input(INPUT_POST, 'szamlazasi_cim', FILTER_SANITIZE_SPECIAL_CHARS));
    // Hűségpontok: engedjük a 0-t is
    $husegpontok_input = filter_input(INPUT_POST, 'husegpontok', FILTER_SANITIZE_NUMBER_INT);
    $husegpontok = ($husegpontok_input !== '' && is_numeric($husegpontok_input)) ? (int)$husegpontok_input : 0;

    // Jelszó mezők (nincs tisztítás, mert speciális karakterek is lehetnek)
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validáció
    $errors = [];
    if (empty($nev)) $errors[] = "A teljes név megadása kötelező.";
    if (empty($emailcim)) {
        $errors[] = "Az email cím megadása kötelező.";
    } elseif (!filter_var($emailcim, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Érvénytelen email cím formátum.";
    }
    if ($husegpontok < 0) { // Negatív pont ne legyen
        $errors[] = "A hűségpontok száma nem lehet negatív.";
    }

    // Jelszó validáció (csak ha megadtak újat)
    $update_password = false;
    $hashed_password = null;
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "Az új jelszavak nem egyeznek.";
        } else {
            // Opcionális: Jelszó erősség ellenőrzése (pl. minimum hossz)
            if (strlen($new_password) < 6) { // Példa: min 6 karakter
                $errors[] = "Az új jelszónak legalább 6 karakter hosszúnak kell lennie.";
            } else {
                // Jelszó hashelése
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                if ($hashed_password === false) {
                    $errors[] = "Hiba történt a jelszó feldolgozása során.";
                    error_log("Password hash hiba felhasználónál: " . $felhasznalo_nev_post);
                } else {
                    $update_password = true;
                }
            }
        }
    }

    // Ha vannak validációs hibák
    if (!empty($errors)) {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bevitt adatokban:<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $errors)) . '</li></ul></div>';
        // Nem irányítunk át, az űrlap újratöltődik a POST adatokkal (lásd HTML value attribútumok lejjebb)
        // Hogy az űrlap a POST adatokat mutassa hiba esetén:
        $user['nev'] = $nev;
        $user['emailcim'] = $emailcim;
        $user['szamlazasi_cim'] = $szamlazasi_cim;
        $user['husegpontok'] = $husegpontok;
        // A jelszó mezőket nem töltjük újra!
    } else {
        // --- Adatbázis frissítése ---
        try {
            // SQL lekérdezés összeállítása
            $sql_parts = [
                "nev = ?",
                "emailcim = ?",
                "szamlazasi_cim = ?",
                "husegpontok = ?",
            ];
            $params = [$nev, $emailcim, $szamlazasi_cim, $husegpontok];
            $types = "sssi"; // nev, email, cim, husegp, admin

            if ($update_password) {
                $sql_parts[] = "jelszo = ?";
                $params[] = $hashed_password;
                $types .= "s"; // jelszo
            }

            // Felhasználónév a WHERE feltételhez
            $params[] = $felhasznalo_nev_post;
            $types .= "s"; // felhasznalo_nev

            $sql = "UPDATE felhasznalo SET " . implode(", ", $sql_parts) . " WHERE felhasznalo_nev = ?";

            // Adatbázis kapcsolat ellenőrzése (ping)
            if (!$db->ping()) {
                error_log("Adatbázis kapcsolat hiba (ping failed) felhasználó frissítése előtt: " . $felhasznalo_nev_post);
                throw new Exception("Adatbázis kapcsolati hiba. Kérjük, próbálja újra.");
            }

            $modositas = $db->prepare($sql);
            if ($modositas === false) {
                error_log("SQL prepare hiba (felhasználó frissítés): " . $db->error . " SQL: " . $sql);
                throw new Exception("Hiba történt a módosítás előkészítésekor.");
            }

            // Paraméterek kötése dinamikusan
            $modositas->bind_param($types, ...$params); // Splat operator (...) PHP 5.6+

            if ($modositas->execute()) {
                $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Felhasználó (' . htmlspecialchars($felhasznalo_nev_post) . ') sikeresen módosítva!</div>';
                $modositas->close();

                // Átirányítás PRG mintával
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($felhasznalo_nev_post) . "&status=ok");
                exit;
            } else {
                error_log("SQL execute hiba (felhasználó frissítés): " . $modositas->error . " (Felhasználónév: " . $felhasznalo_nev_post . ")");
                // Lehet, hogy az email már foglalt?
                if ($db->errno === 1062) { // Duplicate entry hibakód (általában)
                    throw new Exception("Hiba a módosítás során: Az email cím ('" . htmlspecialchars($emailcim) . "') már foglalt lehet.");
                } else {
                    throw new Exception("Hiba történt a módosítás során az adatbázisban. (" . htmlspecialchars($modositas->error) . ")");
                }
            }
            // Close statement if execute failed but prepare was successful
            if ($modositas) $modositas->close();
        } catch (Exception $e) {
            // Hibakezelés a try blokkból
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
            // Hiba esetén is átirányíthatunk, hogy ne ragadjon be a POST
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($felhasznalo_nev_post) . "&status=error");
            exit;
        }
    } // Validációs hibák else ágának vége
} // POST kérés vége

?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználó Módosítása</title>
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

    <h1>Felhasználó Módosítása: <?= htmlspecialchars($user['felhasznalo_nev'] ?? '') ?></h1>
    <hr>

    <div>
        <!-- Üzenetek -->
        <?php
        if (isset($_SESSION['uzenet'])) {
            echo $_SESSION['uzenet'];
            unset($_SESSION['uzenet']);
        }
        ?>
    </div>

    <div class="menu mb-3">
        <a href="./admin_jogosultsag.php"><button type="button">Vissza a felhasználókhoz</button></a>
    </div>

    <?php if (isset($user)): // Csak akkor jelenítjük meg a formot, ha $user létezik 
    ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?id=<?= urlencode($felhasznalo_nev_get) ?>" class="form needs-validation" id="modositas-form" novalidate>

            <input type="hidden" name="felhasznalo_nev" value="<?= htmlspecialchars($user['felhasznalo_nev'] ?? '') ?>">

            <label for="fnev_display" class="form-label">Felhasználónév:</label>
            <input type="text" id="fnev_display" class="form-control" value="<?= htmlspecialchars($user['felhasznalo_nev'] ?? '') ?>" disabled readonly>
            <div class="form-text">A felhasználónév nem módosítható.</div>


            <label for="nev" class="form-label">Teljes Név:</label>
            <input type="text" id="nev" name="nev" class="form-control" value="<?= htmlspecialchars($user['nev'] ?? '') ?>" required>
            <div class="invalid-feedback">
                A teljes név megadása kötelező.
            </div>


            <label for="emailcim" class="form-label">Email Cím:</label>
            <input type="email" id="emailcim" name="emailcim" class="form-control" value="<?= htmlspecialchars($user['emailcim'] ?? '') ?>" required>
            <div class="invalid-feedback">
                Kérjük, érvényes email címet adjon meg.
            </div>


            <label for="szamlazasi_cim" class="form-label">Számlázási Cím:</label>
            <textarea id="szamlazasi_cim" name="szamlazasi_cim" class="form-control" rows="3"><?= htmlspecialchars($user['szamlazasi_cim'] ?? '') ?></textarea>


            <label for="husegpontok" class="form-label">Hűségpontok:</label>
            <input type="number" id="husegpontok" name="husegpontok" class="form-control" value="<?= htmlspecialchars($user['husegpontok'] ?? '0') ?>" required min="0">
            <div class="invalid-feedback">
                A hűségpontoknak nem negatív számnak kell lennie.
            </div>

            <!-- Jelszó Módosítás Szekció -->
            <fieldset class="password-section">
                <legend>Jelszó Módosítása (opcionális)</legend>
                <div class="form-text">Csak akkor töltse ki, ha meg akarja változtatni a felhasználó jelszavát.</div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">Új jelszó:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" aria-describedby="passwordHelp">
                    <div id="passwordHelp" class="form-text">Legalább 6 karakter.</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Új jelszó megerősítése:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                </div>
            </fieldset>
            <!-- Jelszó Módosítás Vége -->

            <button type="submit" name="update_user" class="btn btn-primary">Módosítások Mentése</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            A felhasználó adatai nem érhetők el. <a href="./admin_jogosultsag.php">Vissza a listához</a>.
        </div>
    <?php endif; // vége if ($user) 
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

        // Bootstrap kliens oldali validáció engedélyezése (alap)
        // Működik a 'required' attribútumokkal és az input type="email/number" stb. validációval
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    // Extra jelszó ellenőrzés (ha megadtak újat)
                    const newPassword = form.querySelector('#new_password');
                    const confirmPassword = form.querySelector('#confirm_password');
                    if (newPassword && confirmPassword && newPassword.value !== '') {
                        if (newPassword.value !== confirmPassword.value) {
                            confirmPassword.setCustomValidity("A két jelszó nem egyezik."); // Egyéni hibaüzenet
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            confirmPassword.setCustomValidity(""); // Hiba törlése, ha egyeznek
                        }
                        // Opcionális: erősség ellenőrzése itt is
                        if (newPassword.value.length < 6) {
                            newPassword.setCustomValidity("A jelszónak legalább 6 karakternek kell lennie.");
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            newPassword.setCustomValidity("");
                        }
                    } else if (confirmPassword) {
                        confirmPassword.setCustomValidity(""); // Töröljük a hibát, ha nincs új jelszó
                    }


                    form.classList.add('was-validated')
                }, false)

                // Hibaüzenet törlése gépeléskor a confirm mezőben
                const confirmPasswordInput = form.querySelector('#confirm_password');
                if (confirmPasswordInput) {
                    confirmPasswordInput.addEventListener('input', () => {
                        confirmPasswordInput.setCustomValidity('');
                    });
                }
                const newPasswordInput = form.querySelector('#new_password');
                if (newPasswordInput) {
                    newPasswordInput.addEventListener('input', () => {
                        newPasswordInput.setCustomValidity('');
                    });
                }

            })
        })()
    </script>

</body>

</html>