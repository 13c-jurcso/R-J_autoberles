<?php
session_start();

$conn = new mysqli("localhost", "root", "", "autoberles");

if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM jarmuvek WHERE jarmu_id = $carId";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $car = $result->fetch_assoc();
    // A képek JSON-ként vannak tárolva az adatbázisban, tehát a json_decode függvénnyel átalakítjuk tömbbé.
    $carImages = json_decode($car['kep_url']);
    var_dump($car['kep_url']);  // Ellenőrizd, hogy mit tartalmaz a 'kep_url' mező

}

$queryReviews = "SELECT * FROM velemenyek WHERE jarmu_id = $carId ORDER BY datum DESC";
$resultReviews = $conn->query($queryReviews);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['gyarto']) ?> <?= htmlspecialchars($car['tipus']) ?> Részletek</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/auto_adatok.css">
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
        <?php if (isset($car)): ?>
        <h1><?= htmlspecialchars($car['gyarto']) ?> <?= htmlspecialchars($car['tipus']) ?></h1>
        
        <!-- Képgaléria -->
        <div class="car-gallery">
            <?php if (!empty($carImages)): ?>
                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($carImages as $index => $image): ?>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $index ?>" <?= $index === 0 ? 'class="active"' : '' ?> aria-current="true" aria-label="Slide <?= $index + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($carImages as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($image) ?>" class="d-block w-100" alt="Car Image">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            <?php else: ?>
                <p>Nem található kép ehhez az autóhoz.</p>
            <?php endif; ?>
        </div>

        <p><strong>Motor:</strong> <?= htmlspecialchars($car['motor']) ?></p>
        <p><strong>Év:</strong> <?= htmlspecialchars($car['gyartasi_ev']) ?></p>
        <p><strong>Leírás:</strong> <?= nl2br(htmlspecialchars($car['leiras'])) ?></p>
        <p><strong>Ár:</strong> <?= number_format($car['ar'], 0, '.', ' ') ?> Ft</p>
        <hr>

        <h3>Vélemények</h3>
        <div class="reviews">
            <?php
            if ($resultReviews->num_rows > 0):
                while ($review = $resultReviews->fetch_assoc()):
            ?>
            <div class="review">
                <strong><?= htmlspecialchars($review['felhasznalo_nev']) ?>:</strong>
                <p><?= nl2br(htmlspecialchars($review['uzenet'])) ?></p>
                <small><?= htmlspecialchars($review['datum']) ?></small>
            </div>
            <?php endwhile; else: ?>
            <p>Nincs vélemény erről az autóról.</p>
            <?php endif; ?>
        </div>

        <form action="cseveges.php" method="post">
            <input type="hidden" name="jarmu_id" value="<?= $carId ?>">
            <textarea name="message" rows="5" placeholder="Írd le véleményed" required></textarea>
            <button type="submit">Vélemény küldése</button>
        </form>
        <?php else: ?>
        <p>Ez az autó nem található.</p>
        <?php endif; ?>
    </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap 5 JS -->
</body>
</html>

<?php
$conn->close();
?>
