<?php
include "./db_connection.php";
include "./adatLekeres.php";

// PHPMailer betöltése
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../php/vendor/autoload.php';

session_start();

// Véleményre válaszadás és e-mail küldés PHPMailer-rel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valasz_submit'])) {
    $velemeny_id = $_POST['velemeny_id'];
    $admin_valasz = $_POST['admin_valasz'];

    // 1. Válasz mentése az adatbázisba
    $stmt = $db->prepare("UPDATE velemenyek SET admin_valasz = ? WHERE velemeny_id = ?");
    $stmt->bind_param("si", $admin_valasz, $velemeny_id);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Válasz sikeresen elküldve!</div>';

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
            $message = "Kedves Felhasználó,\n\n" .
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
                $_SESSION['uzenet'] .= '<div class="sikeres" id="animDiv">E-mail sikeresen elküldve a felhasználónak!</div>';
            } catch (Exception $e) {
                $_SESSION['uzenet'] .= '<div class="sikertelen" id="animDiv">Hiba az e-mail küldése során: ' . $mail->ErrorInfo . '</div>';
            }
        } else {
            $_SESSION['uzenet'] .= '<div class="sikertelen" id="animDiv">Nem található e-mail cím a felhasználóhoz!</div>';
        }
    } else {
        $_SESSION['uzenet'] = '<div class="sikertelen" id="animDiv">Hiba a válasz mentése során!</div>';
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
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="husegpontok.php">Hűségpontok</a></li>
                <li><a href="jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Vélemények kezelése</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button id="jarmuvek" onclick="mutatResz('resz1')">Járművek <svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                <path
                    d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
            </svg>
        </button></a>
        <a href="./admin_jogosultsag.php"><button id="jogosultsag" onclick="mutatResz('resz2')">Jogosultságok <svg xmlns="http://www.w3.org/2000/svg"
                width="16" height="16" fill="currentColor" class="bi bi-person-fill-down" viewBox="0 0 16 16">
                <path
                    d="M12.5 9a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7m.354 5.854 1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V10.5a.5.5 0 0 0-1 0v2.793l-.646-.647a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                <path
                    d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
            </svg>
        </button></a>
        <a href="./admin_berlesek.php"><button id="berlesek">Bérlések <svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                <path
                    d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
            </svg>
        </button></a>
        <a href="./admin_velemenyek.php"><button>Vélemények</button></a>
        <a href="./admin_akciok.php"><button>Akciók</button></a>
    </div>

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
                           LEFT JOIN jarmuvek j ON v.jarmu_id = j.jarmu_id";
        $velemenyek = adatokLekerese($velemenyek_sql);

        echo '<table><tr><th>ID</th><th>Felhasználó</th><th>Vélemény</th><th>Dátum</th><th>Jármű</th><th>Admin válasz</th><th>Művelet</th></tr>';
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
                echo '<td><form method="POST">
                            <input type="hidden" name="velemeny_id" value="' . $v['velemeny_id'] . '">
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

    <script>
        document.getElementById("animDiv")?.addEventListener("click", function() {
            this.classList.add("hidden");
        });
    </script>
</body>
</html>
<?php $db->close(); ?>