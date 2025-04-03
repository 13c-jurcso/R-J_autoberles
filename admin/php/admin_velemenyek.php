<?php
include "./db_connection.php";
include "./adatLekeres.php";
session_start();

if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}
// PHPMailer betöltése
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../php/vendor/autoload.php';


// Vélemény törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_velemeny'])) {
    $vid = $_POST['velemeny_id'];

    $torles = $db->prepare("DELETE FROM `velemenyek` WHERE velemenyek.velemeny_id = ?;");
    $torles->bind_param("i", $vid);

    if ($torles->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">
                                    Sikeres törlés!
                                </div>';
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">
                                    Hiba a törlés során!
                                </div>';
        var_dump($torles->error);
    }
    $torles->close();
}

// Véleményre válaszadás és e-mail küldés PHPMailer-rel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valasz_submit'])) {
    $velemeny_id = $_POST['velemeny_id'];
    $admin_valasz = $_POST['admin_valasz'];
    $felhasznalo_nev = $_POST['felhasznalo_nev'];

    // 1. Válasz mentése az adatbázisba
    $stmt = $db->prepare("UPDATE velemenyek SET admin_valasz = ? WHERE velemeny_id = ?");
    $stmt->bind_param("si", $admin_valasz, $velemeny_id);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">Válasz sikeresen elküldve!</div>';

        // 2. Felhasználó e-mail címének lekérése
        $felhasznalo_sql = "SELECT f.emailcim 
                            FROM felhasznalo f 
                            INNER JOIN velemenyek v ON f.felhasznalo_nev = v.felhasznalo_nev 
                            WHERE v.velemeny_id = ?";
        $stmt_email = $db->prepare($felhasznalo_sql);
        $stmt_email->bind_param("i", $velemeny_id);
        $stmt_email->execute();
        $result = $stmt_email->get_result();
        $felhasznalo = $result->fetch_assoc();
        $stmt_email->close();

        if ($felhasznalo && !empty($felhasznalo['emailcim'])) {
            $to = $felhasznalo['emailcim'];
            $subject = "Válasz a véleményére - Autóbérlés";
            $message = "Kedves $felhasznalo_nev,\n\n" .
                       "Az Ön véleményére az alábbi választ kaptuk adminisztrátorunktól:\n\n" .
                       "\"$admin_valasz\"\n\n" .
                       "Köszönjük, hogy megosztotta velünk véleményét!\n" .
                       "Üdvözlettel,\nAutóbérlés Csapata";

            
            $mail = new PHPMailer(true);
            try {
               
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';          
                $mail->SMTPAuth = true;
                $mail->Username = '13c-jurcso@ipari.vein.hu'; 
                $mail->Password = 'wnbd fotg aszs yseh';           
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

              
                $mail->setFrom('13c-jurcso@ipari.vein.hu', 'Autóbérlés');
                $mail->addAddress($to);

                
                $mail->isHTML(false);                     
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->CharSet = 'UTF-8';

                
                $mail->send();
                $_SESSION['uzenet'] .= '<div class="alert alert-success" role="alert">Email sikeresen elküldve a felhasználónak!</div>';
            } catch (Exception $e) {
                $_SESSION['uzenet'] .= '<div class="alert alert-danger" role="alert">Hiba az e-mail küldése során: ' . $mail->ErrorInfo . '</div>';
            }
        } else {
            $_SESSION['uzenet'] .= '<div class="alert alert-danger" role="alert">Nem található e-mail cím a felhasználóhoz!</div>';
        }
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">Hiba a válasz mentése során!</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vélemények kezelése</title>
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
    <h1>Vélemények</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek">Járművek</button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag">Jogosultságok</button></a>
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

    <div class="table-container">
        <h2>Felhasználói vélemények</h2>
        <?php
        $velemenyek_sql = "SELECT v.velemeny_id, v.felhasznalo_nev, v.uzenet, v.datum, v.admin_valasz, v.jarmu_id, j.gyarto, j.tipus 
                           FROM velemenyek v 
                           LEFT JOIN jarmuvek j ON v.jarmu_id = j.jarmu_id ORDER BY v.velemeny_id DESC;";
        $velemenyek = adatokLekerese($velemenyek_sql);

        echo '<table><tr><th>ID</th><th>Felhasználó</th><th>Vélemény</th><th>Dátum</th><th>Jármű</th><th>Admin válasz</th><th></th><th>Művelet</th></tr>';
        if (is_array($velemenyek)) {
            foreach ($velemenyek as $v) {
                $jarmu = isset($v['jarmu_id']) && $v['jarmu_id'] > 0 ? "{$v['gyarto']} {$v['tipus']}" : "Nincs hozzárendelve";
                echo '<tr>';
                echo '<td>' . $v['velemeny_id'] . '</td>';
                echo '<td>' . $v['felhasznalo_nev'] . '</td>';
                echo '<td>' . $v['uzenet'] . '</td>';
                echo '<td>' . $v['datum'] . '</td>';
                echo '<td>' . $jarmu . '</td>';
                echo '<td>' . ($v['admin_valasz'] ?? 'Nincs válasz') . '</td>';
                echo '<td><form method="POST" onsubmit="return confirm(`Biztosan törölni kívánja ezt a véleményt?`);">
                        <input type="hidden" name="velemeny_id" value="' . $v['velemeny_id'] . '">
                        <button type="submit" class="torles_button" name="delete_velemeny">Törlés</button>
                    </form></td>';
                echo '<td><form method="POST">
                            <input type="hidden" name="velemeny_id" value="' . $v['velemeny_id'] . '">
                            <input type="hidden" name="felhasznalo_nev" value="' . $v['felhasznalo_nev'] . '">
                            <textarea name="admin_valasz" placeholder="Írja meg válaszát"></textarea>
                            <button type="submit" name="valasz_submit">Válasz</button>
                          </form></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="7">Nincs vélemény az adatbázisban.</td></tr>';
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