<?php
session_start();
include "../db_connection.php"; // Adatbázis kapcsolat

$redirect_url = '../admin_berlesek.php'; // Visszairányítási cél

if (!isset($db) || $db->connect_error) {
    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Adatbázis kapcsolati hiba a művelet során.</div>';
    header('Location: ' . $redirect_url);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    // Csak trim és alap htmlspecialchars, mivel a felhasználónév lehet speciálisabb
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// --- Műveletek kezelése ---

switch ($method) {
    case 'POST': // Új bérlés hozzáadása
        // Adatok fogadása és tisztítása
        $jarmu_id = filter_input(INPUT_POST, 'jarmu_id', FILTER_VALIDATE_INT);
        // *** JAVÍTÁS: Felhasználónév fogadása ***
        $felhasznalo = sanitize_input($_POST['felhasznalo'] ?? '');
        $tol = sanitize_input($_POST['tol'] ?? '');
        $ig = sanitize_input($_POST['ig'] ?? '');

        // Validáció
        $errors = [];
        if (!$jarmu_id || $jarmu_id <= 0) $errors[] = "Érvénytelen jármű ID.";
        // *** JAVÍTÁS: Felhasználónév validálása ***
        if (empty($felhasznalo)) $errors[] = "A felhasználónév megadása kötelező.";
        // Itt ellenőrizni kellene, hogy létezik-e ilyen felhasználónév a `felhasznalo` táblában!
        // $checkUserStmt = $db->prepare("SELECT COUNT(*) FROM felhasznalo WHERE felhasznalo_nev = ?");
        // $checkUserStmt->bind_param("s", $felhasznalo);
        // $checkUserStmt->execute();
        // $checkUserStmt->bind_result($userCount);
        // $checkUserStmt->fetch();
        // $checkUserStmt->close();
        // if ($userCount == 0) $errors[] = "A megadott felhasználónév nem létezik.";

        if (!validate_date($tol)) $errors[] = "Érvénytelen átvételi dátum formátum (YYYY-MM-DD).";
        if (!validate_date($ig)) $errors[] = "Érvénytelen leadási dátum formátum (YYYY-MM-DD).";
        if (validate_date($tol) && validate_date($ig) && strtotime($ig) < strtotime($tol)) {
             $errors[] = "A leadás dátuma nem lehet korábbi az átvétel dátumánál.";
        }
        // További validációk...


        if (!empty($errors)) {
            $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a hozzáadás során:<ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
        } else {
            // *** JAVÍTÁS: Prepared Statement - bind_param típusa 's' a felhasználónévnek ***
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
        break;

    case 'PUT': // Bérlés módosítása
        // Adatok fogadása és tisztítása
        $berles_id = filter_input(INPUT_POST, 'berles_id', FILTER_VALIDATE_INT);
        $jarmu_id = filter_input(INPUT_POST, 'jarmu_id', FILTER_VALIDATE_INT);
        // *** JAVÍTÁS: Felhasználónév fogadása ***
        $felhasznalo = sanitize_input($_POST['felhasznalo'] ?? '');
        $tol = sanitize_input($_POST['tol'] ?? '');
        $ig = sanitize_input($_POST['ig'] ?? '');

         // Validáció
        $errors = [];
        if (!$berles_id || $berles_id <= 0) $errors[] = "Érvénytelen bérlés ID a módosításhoz.";
        if (!$jarmu_id || $jarmu_id <= 0) $errors[] = "Érvénytelen jármű ID.";
        // *** JAVÍTÁS: Felhasználónév validálása ***
        if (empty($felhasznalo)) $errors[] = "A felhasználónév megadása kötelező.";
        // Itt is ellenőrizni kellene, hogy létezik-e a megadott felhasználónév! (Lásd POST résznél)

        if (!validate_date($tol)) $errors[] = "Érvénytelen átvételi dátum formátum (YYYY-MM-DD).";
        if (!validate_date($ig)) $errors[] = "Érvénytelen leadási dátum formátum (YYYY-MM-DD).";
        if (validate_date($tol) && validate_date($ig) && strtotime($ig) < strtotime($tol)) {
             $errors[] = "A leadás dátuma nem lehet korábbi az átvétel dátumánál.";
        }
        // További validációk...

        if (!empty($errors)) {
             $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a módosítás során:<ul><li>' . implode('</li><li>', $errors) . '</li></ul></div>';
        } else {
            // *** JAVÍTÁS: Prepared Statement - bind_param típusa 's' a felhasználónévnek ***
            $stmt = $db->prepare("UPDATE berlesek SET jarmu_id = ?, felhasznalo = ?, tol = ?, ig = ? WHERE berles_id = ?");
             if ($stmt === false) {
                $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az SQL előkészítésekor (UPDATE).</div>';
                error_log("SQL Prepare Hiba (UPDATE berlesek): " . $db->error);
            } else {
                // jarmu_id (i), felhasznalo (s), tol (s), ig (s), berles_id (i) -> isssi
                $stmt->bind_param("isssi", $jarmu_id, $felhasznalo, $tol, $ig, $berles_id);
                 if ($stmt->execute()) {
                     if ($stmt->affected_rows > 0) {
                        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Bérlés (ID: '.$berles_id.') sikeresen módosítva!</div>';
                     } else {
                         $_SESSION['uzenet'] = '<div class="alert alert-warning" role="alert">Nem történt módosítás (lehet, hogy az adatok nem változtak, vagy a bérlés ID érvénytelen).</div>';
                     }
                 } else {
                    $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a bérlés módosításakor: ' . htmlspecialchars($stmt->error) . '</div>';
                    error_log("SQL Execute Hiba (UPDATE berlesek): " . $stmt->error);
                 }
                 $stmt->close();
            }
        }
        break;

    case 'DELETE': // Bérlés törlése (Ez változatlan maradhat, mert berles_id alapján töröl)
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
                         $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Bérlés (ID: '.$berles_id.') sikeresen törölve!</div>';
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
        break;

    default:
        $_SESSION['uzenet'] = '<div class="alert alert-warning" role="alert">Nem támogatott művelet.</div>';
        break;
}

// Visszairányítás
header('Location: ' . $redirect_url);
exit;
?>