<?php
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;
session_start();

if (!isset($_SESSION['felhasznalo_nev'])) {
    echo '<script type="text/javascript">',
         'alert("Kérem jelentkezzen be, hogy tovább tudjon lépni!");',
         'window.location.href = "index.php";',
         '</script>';
    exit();
}

include './adatLekeres.php';

$atvetel = isset($_GET['atvetel']) ? $_GET['atvetel'] : null;
$leadas = isset($_GET['leadas']) ? $_GET['leadas'] : null;
$kategoria = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;
$min_ar = isset($_GET['min_ar']) ? (int)$_GET['min_ar'] : null;
$max_ar = isset($_GET['max_ar']) ? (int)$_GET['max_ar'] : null;

$sql = "SELECT * FROM jarmuvek WHERE 1=1";
$params = [];
$types = "";

if ($atvetel && $leadas) {
    $sql .= " AND jarmu_id NOT IN (
        SELECT jarmu_id FROM berlesek
        WHERE NOT ((tol > ? AND tol >= ?) OR (ig < ? AND ig <= ?))
    )";
    array_push($params, $atvetel, $leadas, $atvetel, $leadas);
    $types .= "ssss";
}

if ($kategoria) {
    $sql .= " AND felhasznalas_id = ?";
    array_push($params, $kategoria);
    $types .= "s";
}

if ($min_ar) {
    $sql .= " AND ar >= ?";
    array_push($params, $min_ar);
    $types .= "i";
}
if ($max_ar) {
    $sql .= " AND ar <= ?";
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

$conn = new mysqli("localhost", "root", "", "autoberles");
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$user_query = "SELECT nev, emailcim FROM felhasznalo WHERE felhasznalo_nev = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $_SESSION['felhasznalo_nev']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

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
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $jarmu_id, $felhasznalo, $berles_tol, $berles_ig, $fizetes_mod);

    if ($stmt->execute()) {
        $vehicle_query = "SELECT gyarto, tipus, ar FROM jarmuvek WHERE jarmu_id = ?";
        $vehicle_stmt = $conn->prepare($vehicle_query);
        $vehicle_stmt->bind_param("i", $jarmu_id);
        $vehicle_stmt->execute();
        $vehicle_result = $vehicle_stmt->get_result();
        $vehicle_data = $vehicle_result->fetch_assoc();

        // PHPMailer betöltése
        require 'src/PHPMailer.php';
        require 'src/SMTP.php';
        require 'src/Exception.php';

       

        $mail = new PHPMailer(true);

        try {
            // Szerver beállítások
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '13c-jurcso@ipari.vein.hu'; // Cseréld ki a saját Gmail címedre
            $mail->Password = 'wnbd fotg aszs yseh';    // Alkalmazás-specifikus jelszó
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Küldő és címzett
            $mail->setFrom('13c-jurcso@ipari.vein.hu', 'R&J Autókölcsönző');
            $mail->addAddress($email, $user_data['nev']);

            // Email tartalom
            $mail->isHTML(true);
            $mail->Subject = 'Sikeres bérlés - R&J Autókölcsönző';
            $mail->Body = "
            <html>
            <head>
                <title>Sikeres bérlés</title>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    h2 { color: #2c3e50; }
                    .details { background-color: #f9f9f9; padding: 15px; border-radius: 5px; }
                    .footer { margin-top: 20px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Kedves " . htmlspecialchars($user_data['nev']) . "!</h2>
                    <p>Örömmel értesítjük, hogy bérlése sikeresen rögzítésre került az R&J Autókölcsönző rendszerében.</p>
                    <div class='details'>
                        <h3>Bérlés részletei</h3>
                        <p><strong>Jármű:</strong> " . htmlspecialchars($vehicle_data['gyarto'] . " " . $vehicle_data['tipus']) . "</p>
                        <p><strong>Bérlés kezdete:</strong> " . htmlspecialchars($berles_tol) . "</p>
                        <p><strong>Bérlés vége:</strong> " . htmlspecialchars($berles_ig) . "</p>
                        <p><strong>Napi ár:</strong> " . number_format($vehicle_data['ar'], 0, '.', ' ') . " Ft</p>
                        <p><strong>Fizetési mód:</strong> " . ($fizetes_mod ? "Azonnal" : "Helyszínen") . "</p>
                    </div>
                    <p>Köszönjük, hogy minket választott! Kérdés esetén keressen minket az info@nemletezokft.hu címen.</p>
                    <div class='footer'>
                        <p>R&J Autókölcsönző © 2025</p>
                    </div>
                </div>
            </body>
            </html>";

            $mail->send();
            echo "<script>alert('A bérlés sikeresen rögzítve! Ellenőrizze email fiókját a részletekért.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Hiba történt az email küldésekor: " . $mail->ErrorInfo . "');</script>";
        }
    } else {
        echo "<script>alert('Hiba történt a bérlés mentésekor: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Járművek</title>
    <script defer src="../jarmuvek.js"></script>
    <link rel="stylesheet" href="../css/jarmuvek.css">
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <form method="GET" action="jarmuvek.php">
        <h2>Elérhető járművek</h2>
        <hr>
        <label for="atvetel">Átvétel dátuma:</label>
        <input type="date" id="atvetel" name="atvetel" value="<?= htmlspecialchars($atvetel) ?>">

        <label for="leadas">Leadás dátuma:</label>
        <input type="date" id="leadas" name="leadas" value="<?= htmlspecialchars($leadas) ?>">

        <label for="kategoria">Kategória: </label>
        <select id="kategoria" eld="rental_date" name="kategoria">
            <option value="">-- Válassz kategóriát --</option>
            <option value="1">Városi</option>
            <option value="2">Családi</option>
            <option value="3">Haszon</option>
            <option value="4">Élmény</option>
            <option value="5">Lakó</option>
        </select>

        <label for="min_ar">Minimum ár:</label>
        <input type="number" id="min_ar" name="min_ar" value="<?= htmlspecialchars($min_ar) ?>" placeholder="Pl. 10000">

        <label for="max_ar">Maximum ár:</label>
        <input type="number" id="max_ar" name="max_ar" value="<?= htmlspecialchars($max_ar) ?>" placeholder="Pl. 50000">

        <button type="submit" class="btn btn-primary">Szűrés</button>
    </form>
</div>
<div class="card-container">
    <?php if (!empty($jarmuvek)): ?>
        <?php foreach ($jarmuvek as $kocsi): ?>
            <?php
            $carImages = json_decode($kocsi['kep_url']);
            $firstImage = !empty($carImages) ? $carImages[0] : 'default.jpg';
            ?>
            <div class="card">
                <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($kocsi['gyarto']) . ' ' . htmlspecialchars($kocsi['tipus']) ?>" class="card-img">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($kocsi['gyarto']) . ' ' . htmlspecialchars($kocsi['tipus']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($kocsi['leiras']) ?></p>
                    <p class="card-text">Ár: <?= number_format($kocsi['ar'], 0, '.', ' ') ?> Ft/nap</p>
                    <button class="berles-gomb" onclick="openModal(this)" 
                        data-id="<?= htmlspecialchars($kocsi['jarmu_id']) ?>" 
                        data-gyarto="<?= htmlspecialchars($kocsi['gyarto']) ?>" 
                        data-tipus="<?= htmlspecialchars($kocsi['tipus']) ?>">Részletek</button>
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

            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <input type="text" placeholder="Teljes név" id="name" name="name" value="<?= htmlspecialchars($user_data['nev']) ?>" required>
            </div>

            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
                    </svg>
                </span>
                <input type="email" placeholder="user@example.com" id="email" name="email" value="<?= htmlspecialchars($user_data['emailcim']) ?>" required>
            </div>

            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                    </svg>
                </span>
                <input type="tel" placeholder="+36201234567" id="phone" name="phone" required>
            </div>

            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check-fill" viewBox="0 0 16 16">
                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/>
                    </svg>
                </span>
                <input type="date" id="rental_date" name="rental_date" required>
            </div>

            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-x-fill" viewBox="0 0 16 16">
                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2M6.854 8.146 8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 1 1 .708-.708"/>
                    </svg>
                </span>
                <input type="date" id="return_date" name="return_date" required>
            </div>

            <button type="submit" name="fizetes_mod" value="1">Fizetés azonnal</button>
            <button type="submit" name="fizetes_mod" value="0">Fizetés a helyszínen</button>
        </form>
    </div>
</div>

<div id="overlay" class="overlay"></div>
<script>
    function openModal(button) {
        document.getElementById('jarmu_id').value = button.getAttribute('data-id');
        document.getElementById('modal').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
    }
</script>
</body>
</html>