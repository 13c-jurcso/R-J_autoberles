<?php
// Kapcsolat az adatbázissal
include './adatLekeres.php';

// Ha a form elküldésre került
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jarmu_id = $_POST['jarmu_id'];
    $felhasznalo = $_POST['name'];
    $email = $_POST['email'];
    $telefon = $_POST['phone'];
    $berles_tol = $_POST['rental_date'];
    $berles_ig = $_POST['return_date'];

    // Adatok mentése az adatbázisba
    $conn = new mysqli("localhost", "root", "", "autoberles");
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    $sql = "INSERT INTO berlesek (jarmu_id, felhasznalo, tol, ig, kifizetve) 
            VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $jarmu_id, $felhasznalo, $berles_tol, $berles_ig);

    if ($stmt->execute()) {
        echo "<script>alert('A bérlés sikeresen rögzítve!');</script>";
    } else {
        echo "<script>alert('Hiba történt a bérlés mentésekor: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Járművek</title>
    <script defer src="../jarmuvek.js"></script>
    <link rel="stylesheet" href="../css/jarmuvek.css">
</head>
<body>
<header>
    <div class="menu-toggle">☰ Menu</div>
    <nav>
        <ul>
            <li><a href="index.php">R&J</a></li>
            <li><a href="kapcsolat.php">Kapcsolat</a></li>
            <li><a href="husegpontok.php">Hűségpontok</a></li>
            <li><a href="jarmuvek.php">Gépjárművek</a></li>
            <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="filter">
    <button class="szures_gomb" id="szures_gomb">Szűrés <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
        </svg>
    </button>
</div>

<div class="card-container">
    <?php
    $kocsi_kartya_sql = "SELECT jarmu_id, gyarto, tipus, gyartasi_ev, motor, leiras, ar, kep_url FROM jarmuvek;";
    $kocsiKartya = adatokLekerese($kocsi_kartya_sql);
    if (is_array($kocsiKartya)) {
        foreach ($kocsiKartya as $kocsi) {
            echo '<div class="card">';
            echo '<img src="' . $kocsi['kep_url'] . '" alt="' . $kocsi['gyarto'] . '" class="card-image">';
            echo '<div class="card-content">';
            echo '<h3 class="card-title">' . $kocsi['gyarto'] . '</h3>';
            echo '<p class="card-text">' . $kocsi['tipus'] . '</p>';
            echo '<p class="card-text">' . $kocsi['gyartasi_ev'] . '</p>';
            echo '<p class="card-text">' . $kocsi['motor'] . '</p>';
            echo '<p class="card-text">' . $kocsi['leiras'] . '</p>';
            echo '<p class="card-text">' . $kocsi['ar'] . ' Ft</p>';
            echo '</div>';
            echo '<button class="berles-gomb" onclick="openModal(this)" 
                    data-id="' . $kocsi['jarmu_id'] . '" 
                    data-gyarto="' . $kocsi['gyarto'] . '" 
                    data-tipus="' . $kocsi['tipus'] . '">Bérelés</button>';
            echo '</div>';
        }
    }
    ?>
</div>

<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Bérlés adatai</h3>
        <form method="POST">
            <input type="hidden" name="jarmu_id" id="jarmu_id">
            <label for="name">Név:</label>
            <input type="text" id="name" name="name" required><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="phone">Telefonszám:</label>
            <input type="tel" id="phone" name="phone" required><br>
            <label for="rental_date">Bérlés kezdete:</label>
            <input type="date" id="rental_date" name="rental_date" required><br>
            <label for="return_date">Bérlés vége:</label>
            <input type="date" id="return_date" name="return_date" required><br>
            <button type="submit">Bérlés megerősítése</button>
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
