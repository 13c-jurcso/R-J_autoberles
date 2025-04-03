<?php
include "./db_connection.php";
include "./adatLekeres.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../php/src/PHPMailer.php';
require '../../php/src/SMTP.php';
require '../../php/src/Exception.php';

session_start();
if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}
// Felhasználók e-mail címeinek lekérdezése
function sendAkcioEmail($jarmu_nev, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday) {
    global $db;
    $users_sql = "SELECT emailcim, nev FROM felhasznalo";
    $users = adatokLekerese($users_sql);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '13c-jurcso@ipari.vein.hu';
        $mail->Password = 'wnbd fotg aszs yseh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        $mail->setFrom('13c-jurcso@ipari.vein.hu', 'R&J Autókölcsönző');
        foreach ($users as $user) {
            $mail->addAddress($user['emailcim'], $user['nev']);
        }

        $mail->isHTML(true);
        $mail->Subject = $is_black_friday ? 'Black Friday Akció - R&J Autókölcsönző' : 'Új Akció - R&J Autókölcsönző';
        $mail->Body = "
        <html>
        <head>
            <title>Új akció</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
                h2 { color: " . ($is_black_friday ? "#FFD700" : "#2c3e50") . "; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Kedves Ügyfelünk!</h2>
                <p>Új akciót hirdettünk meg:</p>
                <p><strong>Jármű:</strong> $jarmu_nev</p>
                <p><strong>Kedvezmény:</strong> $kedvezmeny_szazalek%</p>
                <p><strong>Kezdete:</strong> $kezdete</p>
                <p><strong>Vége:</strong> $vege</p>
                <p><strong>Leírás:</strong> $leiras</p>
                " . ($is_black_friday ? "<p style='color: red;'>Ez egy Black Friday különleges ajánlat!</p>" : "") . "
                <p>Foglaljon most a jarmuvek.php oldalon!</p>
            </div>
        </body>
        </html>";

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['uzenet'] .= '<div class="alert alert-danger" role="alert">Hiba az e-mail küldésekor: ' . $mail->ErrorInfo . '</div>';
    }
}

// Akció hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_akcio'])) {
    $jarmu_id = $_POST['jarmu_id'];
    $kedvezmeny_szazalek = $_POST['kedvezmeny_szazalek'];
    $kezdete = $_POST['kezdete'];
    $vege = $_POST['vege'];
    $leiras = $_POST['leiras'];
    $is_black_friday = isset($_POST['is_black_friday']) ? 1 : 0;

    $stmt = $db->prepare("INSERT INTO akciok (jarmu_id, kedvezmeny_szazalek, kezdete, vege, leiras, is_black_friday) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssi", $jarmu_id, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Akció sikeresen hozzáadva!</div>';
        $jarmu_sql = "SELECT gyarto, tipus FROM jarmuvek WHERE jarmu_id = ?";
        $jarmu_stmt = $db->prepare($jarmu_sql);
        $jarmu_stmt->bind_param("i", $jarmu_id);
        $jarmu_stmt->execute();
        $jarmu = $jarmu_stmt->get_result()->fetch_assoc();
        $jarmu_nev = $jarmu['gyarto'] . ' ' . $jarmu['tipus'];
        sendAkcioEmail($jarmu_nev, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az akció hozzáadása során!</div>';
    }
    $stmt->close();
}

// Black Friday akció hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_black_friday'])) {
    $jarmu_id = $_POST['jarmu_id'];
    $kedvezmeny_szazalek = 50;
    $kezdete = date('Y-11-28');
    $vege = date('Y-11-30');
    $leiras = "Black Friday különleges ajánlat!";
    $is_black_friday = 1;

    $stmt = $db->prepare("INSERT INTO akciok (jarmu_id, kedvezmeny_szazalek, kezdete, vege, leiras, is_black_friday) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssi", $jarmu_id, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Black Friday akció sikeresen hozzáadva!</div>';
        $jarmu_sql = "SELECT gyarto, tipus FROM jarmuvek WHERE jarmu_id = ?";
        $jarmu_stmt = $db->prepare($jarmu_sql);
        $jarmu_stmt->bind_param("i", $jarmu_id);
        $jarmu_stmt->execute();
        $jarmu = $jarmu_stmt->get_result()->fetch_assoc();
        $jarmu_nev = $jarmu['gyarto'] . ' ' . $jarmu['tipus'];
        sendAkcioEmail($jarmu_nev, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a Black Friday akció hozzáadása során!</div>';
    }
    $stmt->close();
}

// Akció törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_akcio'])) {
    $akcio_id = $_POST['akcio_id'];

    $stmt = $db->prepare("DELETE FROM akciok WHERE akcio_id = ?");
    $stmt->bind_param("i", $akcio_id);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Akció sikeresen törölve!</div>';
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba az akció törlése során!</div>';
    }
    $stmt->close();
}

// Járművek lekérdezése az űrlapokhoz
$jarmuvek_sql = "SELECT jarmu_id, gyarto, tipus FROM jarmuvek";
$jarmuvek = adatokLekerese($jarmuvek_sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akciók kezelése</title>
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
    <h1>Akciók</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek">Járművek</button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag">Jogosultságok </button></a>
        <a href="./admin_berlesek.php"><button type="submit" id="berlesek">Bérlések</button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
    <hr>
    <div>
        <?php
        if (isset($_SESSION['uzenet'])) {
            echo $_SESSION['uzenet'];
            unset($_SESSION['uzenet']);
        }
        ?>
    </div>

    <div class="tartalmi-resz">
        <h2>Új akció hozzáadása</h2>
        <form method="POST" class="form">
            <label>Jármű:</label>
            <select name="jarmu_id" required>
                <?php
                if (is_array($jarmuvek) && !empty($jarmuvek)) {
                    foreach ($jarmuvek as $j) {
                        echo '<option value="' . $j['jarmu_id'] . '">' . $j['gyarto'] . ' ' . $j['tipus'] . '</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }
                ?>
            </select>

            <label>Kedvezmény (%):</label>
            <input type="number" name="kedvezmeny_szazalek" min="1" max="100" required>

            <label>Kezdete:</label>
            <input type="date" name="kezdete" required>

            <label>Vége:</label>
            <input type="date" name="vege" required>

            <label>Leírás:</label>
            <input type="text" name="leiras">

            <label><input type="checkbox" name="is_black_friday"> Black Friday akció</label>

            <button type="submit" name="add_akcio">Hozzáadás</button>
        </form>
    </div>
    <hr><br>
    <div class="tartalmi-resz">
        <h2>Black Friday akció gyors hozzáadása</h2>
        <form method="POST" class="form">
            <label>Jármű:</label>
            <select name="jarmu_id" required>
                <?php
                if (is_array($jarmuvek) && !empty($jarmuvek)) {
                    foreach ($jarmuvek as $j) {
                        echo '<option value="' . $j['jarmu_id'] . '">' . $j['gyarto'] . ' ' . $j['tipus'] . '</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }
                ?>
            </select>
            <br>
            <button type="submit" name="add_black_friday" class="black-friday-btn">Black Friday akció (50%)</button>
        </form>
    </div>
    <hr><br>

    <div class="table-container">
        <h2>Aktuális akciók</h2>
        <?php
        $akciok_sql = "SELECT a.akcio_id, j.gyarto, j.tipus, a.kedvezmeny_szazalek, a.kezdete, a.vege, a.leiras, a.is_black_friday 
                       FROM akciok a 
                       INNER JOIN jarmuvek j ON a.jarmu_id = j.jarmu_id";
        $akciok = adatokLekerese($akciok_sql);

        echo '<table><tr><th>ID</th><th>Jármű</th><th>Kedvezmény (%)</th><th>Kezdete</th><th>Vége</th><th>Leírás</th><th>Black Friday</th><th>Művelet</th></tr>';
        if (is_array($akciok) && !empty($akciok)) {
            foreach ($akciok as $a) {
                echo '<tr>';
                echo '<td>' . $a['akcio_id'] . '</td>';
                echo '<td>' . $a['gyarto'] . ' ' . $a['tipus'] . '</td>';
                echo '<td>' . $a['kedvezmeny_szazalek'] . '</td>';
                echo '<td>' . $a['kezdete'] . '</td>';
                echo '<td>' . $a['vege'] . '</td>';
                echo '<td>' . ($a['leiras'] ?? 'Nincs') . '</td>';
                echo '<td>' . ($a['is_black_friday'] ? 'Igen' : 'Nem') . '</td>';
                echo '<td><form method="POST" onsubmit="return confirm(`Biztosan törölni kívánja ezt az akciót?`);">
                            <input type="hidden" name="akcio_id" value="' . $a['akcio_id'] . '">
                            <button type="submit" name="delete_akcio" class="torles_button">Törlés</button>
                          </form></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">Nincs akció az adatbázisban.</td></tr>';
        }
        echo '</table>';
        ?>
    </div>

    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>
</body>
</html>
<?php $db->close(); ?>