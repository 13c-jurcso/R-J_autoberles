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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
<div class="szures_div">
    <button class="filter" id="szures_gomb">Szűrés <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
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

            <!-- Név Input Group -->
            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <input type="text" placeholder="Teljes név"  id="name" name="name" required>
            </div>

            <!-- Email Input Group -->
            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z"/>
                    </svg>
                </span>
                <input type="email" placeholder="user@example.com" aria-label="" aria-describedby="basic-addon1" id="email" name="email" required>
            </div>

            <!-- Telefonszám Input Group -->
            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                    </svg>
                </span>
                <input type="tel" placeholder="+36201234567" aria-label="" aria-describedby="basic-addon1" id="phone" name="phone" required>
            </div>

            <!-- Bérlés kezdete Input Group -->
            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check-fill" viewBox="0 0 16 16">
                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708.708"/>
                    </svg>
                </span>
                <input type="date" id="rental_date" name="rental_date" required>
            </div>

            <!-- Bérlés vége Input Group -->
            <div class="input-group">
                <span class="input-group-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-x-fill" viewBox="0 0 16 16">
                        <path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2M6.854 8.146 8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 1 1 .708-.708"/>
                    </svg>
                </span>
                <input type="date" id="return_date" name="return_date" required>
            </div>

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
