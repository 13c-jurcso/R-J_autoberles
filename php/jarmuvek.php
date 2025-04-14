<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Adatbázis kapcsolat
include './adatLekeres.php';

// Bejelentkezés ellenőrzése
if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}




// Járművek lekérdezése
$atvetel = isset($_GET['atvetel']) ? $_GET['atvetel'] : null;
$leadas = isset($_GET['leadas']) ? $_GET['leadas'] : null;
$kategoria = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;
$min_ar = isset($_GET['min_ar']) ? (int)$_GET['min_ar'] : null;
$max_ar = isset($_GET['max_ar']) ? (int)$_GET['max_ar'] : null;

$sql = "SELECT j.*, a.kedvezmeny_szazalek, a.kezdete, a.vege 
        FROM jarmuvek j 
        LEFT JOIN akciok a ON j.jarmu_id = a.jarmu_id 
        AND a.kezdete <= CURDATE() AND a.vege >= CURDATE() 
        WHERE 1=1";
$params = [];
$types = "";

if ($atvetel && $leadas) {
    $sql .= " AND j.jarmu_id NOT IN (
        SELECT jarmu_id FROM berlesek
        WHERE NOT ((tol > ? AND tol >= ?) OR (ig < ? AND ig <= ?))
    )";
    array_push($params, $atvetel, $leadas, $atvetel, $leadas);
    $types .= "ssss";
}

if ($kategoria) {
    $sql .= " AND j.felhasznalas_id = ?";
    array_push($params, $kategoria);
    $types .= "s";
}

if ($min_ar) {
    $sql .= " AND j.ar >= ?";
    array_push($params, $min_ar);
    $types .= "i";
}
if ($max_ar) {
    $sql .= " AND j.ar <= ?";
    array_push($params, $max_ar);
    $types .= "i";
}

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$jarmuvek = $result->fetch_all(MYSQLI_ASSOC);

// Felhasználó adatainak lekérdezése
$user_query = "SELECT nev, emailcim, husegpontok FROM felhasznalo WHERE felhasznalo_nev = ?";
$user_stmt = $db->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['felhasznalo_nev']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Bérlés feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jarmu_id = $_POST['jarmu_id'];
    $felhasznalo = $_SESSION['felhasznalo_nev'];
    $email = $_POST['email'];
    $telefon = $_POST['phone'];
    $berles_tol = $_POST['rental_date'];
    $berles_ig = $_POST['return_date'];
    $fizetes_mod = isset($_POST['fizetes_mod']) ? (int)$_POST['fizetes_mod'] : 0;

    $sql = "INSERT INTO berlesek (jarmu_id, felhasznalo, tol, ig, kifizetve) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("isssi", $jarmu_id, $felhasznalo, $berles_tol, $berles_ig, $fizetes_mod);

    if ($stmt->execute()) {
        $vehicle_query = "SELECT gyarto, tipus, ar, 
                          IFNULL(a.kedvezmeny_szazalek, 0) as kedvezmeny_szazalek 
                          FROM jarmuvek j 
                          LEFT JOIN akciok a ON j.jarmu_id = a.jarmu_id 
                          AND a.kezdete <= CURDATE() AND a.vege >= CURDATE() 
                          WHERE j.jarmu_id = ?";
        $vehicle_stmt = $db->prepare($vehicle_query);
        $vehicle_stmt->bind_param("i", $jarmu_id);
        $vehicle_stmt->execute();
        $vehicle_result = $vehicle_stmt->get_result();
        $vehicle_data = $vehicle_result->fetch_assoc();

        $original_ar = $vehicle_data['ar'];
        $kedvezmeny = $vehicle_data['kedvezmeny_szazalek'];
        $akcios_ar = $kedvezmeny > 0 ? $original_ar * (1 - $kedvezmeny / 100) : $original_ar;

        $berles_napok = (strtotime($berles_ig) - strtotime($berles_tol)) / (60 * 60 * 24);
        if ($berles_napok <= 0) {
            $berles_napok = 1;
        }
        $total_cost = $akcios_ar * $berles_napok;
        $husegpontok = floor($total_cost * 0.1);

        error_log("Bérlés: jarmu_id=$jarmu_id, napok=$berles_napok, total_cost=$total_cost, husegpontok=$husegpontok, felhasznalo=$felhasznalo");

        $update_pontok = "UPDATE felhasznalo SET husegpontok = husegpontok + ? WHERE felhasznalo_nev = ?";
        $pont_stmt = $db->prepare($update_pontok);
        $pont_stmt->bind_param("is", $husegpontok, $felhasznalo);
        if (!$pont_stmt->execute()) {
            error_log("Hiba a hűségpontok frissítésekor: " . $pont_stmt->error);
            $_SESSION['alert_message'] = "Hiba a hűségpontok mentésekor: " . $pont_stmt->error;
            $_SESSION['alert_type'] = "warning";
            $stmt->close();
            $db->close();
            exit();
        }
        error_log("Hűségpontok sikeresen frissítve: $husegpontok pont hozzáadva $felhasznalo számára");
        $pont_stmt->close();

        require 'src/PHPMailer.php';
        require 'src/SMTP.php';
        require 'src/Exception.php';

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
            $mail->addAddress($email, $user_data['nev']);

            $mail->isHTML(true);
            $mail->Subject = 'Sikeres bérlés - R&J Autókölcsönző';
            $mail->Body = "
            <html>
            <head>
                <title>Sikeres bérlés</title>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
                    .container { max-width: 600px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
                    h2 { color: #2c3e50; text-align: center; }
                    .details { background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 5px solid #3498db; margin-top: 15px; }
                    .details h3 { color: #3498db; margin-top: 0; }
                    .details p { margin: 8px 0; font-size: 14px; }
                    .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                    strong { color: #2c3e50; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Kedves " . htmlspecialchars($user_data['nev']) . "!</h2>
                    <p>Örömmel értesítjük, hogy bérlése sikeresen rögzítésre került.</p>
                    <div class='details'>
                        <h3>Bérlés részletei</h3>
                        <p><strong>Jármű:</strong> " . htmlspecialchars($vehicle_data['gyarto'] . " " . $vehicle_data['tipus']) . "</p>
                        <p><strong>Bérlés kezdete:</strong> " . htmlspecialchars($berles_tol) . "</p>
                        <p><strong>Bérlés vége:</strong> " . htmlspecialchars($berles_ig) . "</p>
                        <p><strong>Napi ár:</strong> " . number_format($akcios_ar, 0, '.', ' ') . " Ft" . ($kedvezmeny > 0 ? " (eredeti: " . number_format($original_ar, 0, '.', ' ') . " Ft)" : "") . "</p>
                        <p><strong>Teljes költség:</strong> " . number_format($total_cost, 0, '.', ' ') . " Ft</p>
                        <p><strong>Fizetési mód:</strong> " . ($fizetes_mod ? "Azonnal" : "Helyszínen") . "</p>
                        <p><strong>Kapott hűségpontok:</strong> " . $husegpontok . "</p>
                    </div>
                    <p>Köszönjük, hogy minket választott!</p>
                    <div class='footer'>
                        <p>R&J Autókölcsönző © 2025</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            $_SESSION['alert_message'] = "A bérlés sikeresen rögzítve! Ellenőrizze email fiókját a részletekért.";
            $_SESSION['alert_type'] = "success";
        } catch (Exception $e) {
            $_SESSION['alert_message'] = "Hiba történt az email küldésekor: " . $mail->ErrorInfo;
            $_SESSION['alert_type'] = "warning";
        }
    } else {
        $_SESSION['alert_message'] = "Hiba történt a bérlés mentésekor: " . $stmt->error;
        $_SESSION['alert_type'] = "warning";
    }

    $stmt->close();
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> R&J - Járművek</title>
    <script defer src="../jarmuvek.js"></script>
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/jarmuvek.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">R&J</a></li>
                <li><a href="kapcsolat.php">Kapcsolat</a></li>
                <li><a href="forum.php">Fórum</a></li>
                <li><a href="husegpontok.php">Hűségpontok</a></li>
                <li><a href="jarmuvek.php">Gépjárművek</a></li>
                <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                    <li><a href="profilom.php">Profilom</a></li>
                    <li><a href="logout.php">Kijelentkezés</a></li>
                <?php else: ?>
                    <li><a href="#" onclick="openModal('loginModal')">Bejelentkezés</a></li>
                    <li><a href="#" onclick="openModal('registerModal')">Regisztráció</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <div class="szures_div">
        <button type="submit" id="toggleFilterBtn">Szűrők mutatása/elrejtése</button>
        <div id="filterForm" class="filter-form">
            <form method="GET" action="jarmuvek.php">
                <h2>Elérhető járművek</h2>
                <br>
                <label for="atvetel">Átvétel dátuma:</label>
                <input type="date" id="atvetel" name="atvetel" value="<?= htmlspecialchars($atvetel) ?>">
                <br>
                <label for="leadas">Leadás dátuma:</label>
                <input type="date" id="leadas" name="leadas" value="<?= htmlspecialchars($leadas) ?>">
                <br>
                <label for="kategoria">Kategória: </label>
                <select id="kategoria" name="kategoria">
                    <option value="">-- Válassz kategóriát --</option>
                    <option value="1" <?= isset($kategoria) && $kategoria == "1" ? "selected" : "" ?>>Városi</option>
                    <option value="2" <?= isset($kategoria) && $kategoria == "2" ? "selected" : "" ?>>Családi</option>
                    <option value="3" <?= isset($kategoria) && $kategoria == "3" ? "selected" : "" ?>>Haszon</option>
                    <option value="4" <?= isset($kategoria) && $kategoria == "4" ? "selected" : "" ?>>Élmény</option>
                    <option value="5" <?= isset($kategoria) && $kategoria == "5" ? "selected" : "" ?>>Lakó</option>
                </select>
                <br>
                <label for="min_ar">Minimum ár:</label>
                <input type="number" id="min_ar" name="min_ar" value="<?= isset($min_ar) && $min_ar !== 0 ? htmlspecialchars($min_ar) : '' ?>" placeholder="Pl. 10000">
                <br>
                <label for="max_ar">Maximum ár:</label>
                <input type="number" id="max_ar" name="max_ar" value="<?= isset($max_ar) && $max_ar !== 0 ? htmlspecialchars($max_ar) : '' ?>" placeholder="Pl. 50000">
                <br>
                <button type="submit" class="btn btn-primary">Szűrés</button>
            </form>
        </div>
    </div>
    <div class="card-container">
        <?php if (!empty($jarmuvek)): ?>
            <?php foreach ($jarmuvek as $kocsi): ?>
                <?php
                $carImages = json_decode($kocsi['kep_url']);
                $firstImage = !empty($carImages) ? $carImages[0] : 'default.jpg';
                $original_ar = $kocsi['ar'];
                $kedvezmeny = $kocsi['kedvezmeny_szazalek'] ?? 0;
                $akcios_ar = $kedvezmeny > 0 ? $original_ar * (1 - $kedvezmeny / 100) : $original_ar;
                ?>
                <div class="card">
                    <a href="auto_adatok.php?id=<?= htmlspecialchars($kocsi['jarmu_id']) ?>">
                        <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($kocsi['gyarto']) . ' ' . htmlspecialchars($kocsi['tipus']) ?>" class="card-img">
                    </a>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($kocsi['gyarto']) . ' ' . htmlspecialchars($kocsi['tipus']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($kocsi['leiras']) ?></p>
                        <p class="card-text">
                            <?php if ($kedvezmeny > 0): ?>
                                <span style="text-decoration: line-through; color: red;">
                                    Ár: <?= number_format($original_ar, 0, '.', ' ') ?> Ft/nap
                                </span>
                            <?php endif; ?>
                            Ár: <?= number_format($akcios_ar, 0, '.', ' ') ?> Ft/nap
                        </p>
                        <button class="berles-gomb" onclick="openModal(this)"
                            data-id="<?= htmlspecialchars($kocsi['jarmu_id']) ?>"
                            data-gyarto="<?= htmlspecialchars($kocsi['gyarto']) ?>"
                            data-tipus="<?= htmlspecialchars($kocsi['tipus']) ?>">Bérlés</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Jelenleg nincs elérhető jármű.</p>
        <?php endif; ?>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3>Bérlés adatai</h3>
            <form method="POST">
                <input type="hidden" name="jarmu_id" id="jarmu_id">

                <input type="text" placeholder="Teljes név" id="name" name="name" value="<?= htmlspecialchars($user_data['nev']) ?>" required>
                <input type="email" placeholder="user@example.com" id="email" name="email" value="<?= htmlspecialchars($user_data['emailcim']) ?>" required>
                <input type="tel" placeholder="+36201234567" id="phone" name="phone" required>
                <input type="date" id="rental_date" name="rental_date" required>
                <input type="date" id="return_date" name="return_date" required>

                <button type="submit" name="fizetes_mod" value="1">Fizetés azonnal</button>
                <button type="submit" name="fizetes_mod" value="0">Fizetés a helyszínen</button>
            </form>
        </div>
    </div>

    <div id="overlay" class="overlay"></div>

    <?php
    // Modal include és megjelenítés
    if (isset($_SESSION['alert_message'])) {
        include 'modal.php';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.getElementById("alertModal").classList.add("active");
                document.getElementById("overlay").style.display = "block";
            });
          </script>';
    }
    ?>

    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>

    <script>
        function openModal(button) {
            let modal = document.getElementById('modal');
            let alertModal = document.getElementById('alertModal');
            let overlay = document.getElementById('overlay');

            //Ha stringet kapunk akkor az alertModal
            if (typeof button === 'string') {
                if (button === 'alertModal') {
                    alertModal.classList.add('active');
                } else {
                    document.getElementById(button).style.display = 'block';
                }
            } else { //egyébként a béreles modal
                document.getElementById('jarmu_id').value = button.getAttribute('data-id');
                modal.style.display = 'block';
            }
            overlay.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('alertModal').classList.remove('active');
            const overlay = document.getElementById('overlay');
            overlay.style.display = 'none';
            overlay.classList.remove('active'); // Ensure no active class remains
            overlay.style.visibility = 'hidden'; // Ensure visibility is hidden
        }
        document.getElementById('toggleFilterBtn').addEventListener('click', function() {
            const filterForm = document.getElementById('filterForm');
            filterForm.classList.toggle('collapsed');

            // Optional: Change button text based on state
            if (filterForm.classList.contains('collapsed')) {
                this.textContent = 'Szűrők mutatása';
            } else {
                this.textContent = 'Szűrők elrejtése';
            }
        });
    </script>
</body>

</html>