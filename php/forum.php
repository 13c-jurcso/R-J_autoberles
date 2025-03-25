<?php
session_start();

// Modal include
if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}

$conn = new mysqli("localhost", "root", "", "autoberles");

if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM jarmuvek WHERE jarmu_id = $carId";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $car = $result->fetch_assoc();
    $carImages = json_decode($car['kep_url']);
    var_dump($car['kep_url']);
}
$query = "SELECT * FROM jarmuvek";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fórum</title>
    <link rel="stylesheet" href="../css/forum.css">
    <link rel="stylesheet" href="../css/styles.css">
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

    <div class="container mt-5">
    <h1>Autó Fórum</h1>
    <div class="row">
        <?php
        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $carImages = json_decode($row['kep_url']);
                if (is_array($carImages) && count($carImages) > 0):
        ?>
        <div class="col-md-4">
            <a href="auto_adatok.php?id=<?= $row['jarmu_id'] ?>" class="card-link">
                <div class="card">
                    <img src="<?= htmlspecialchars($carImages[0]) ?>" class="d-block w-100" alt="Car Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['gyarto']) ?> <?= htmlspecialchars($row['tipus']) ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <?php
                endif;
            endwhile;
        else:
            echo "<p>Nincs elérhető autó.</p>";
        endif;
        ?>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>