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

$queryReviews = "SELECT * FROM velemenyek WHERE jarmu_id = $carId ORDER BY datum DESC";
$resultReviews = $conn->query($queryReviews);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['gyarto']) ?> <?= htmlspecialchars($car['tipus']) ?> Részletek</title>
    <link rel="stylesheet" href="../css/auto_adatok.css">
    <link rel="stylesheet" href="../css/style.css">
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
        
        <div class="car-gallery">
    <?php if (!empty($carImages)): ?>
        <div class="main-image">
            <img src="<?= htmlspecialchars($carImages[0]) ?>" class="main-car-image" alt="Main Car Image" onclick="openGallery(0)">
        </div>

        <div id="galleryModal" class="gallery-modal">
            <span class="close-gallery" onclick="closeGallery()">×</span>
            <div class="gallery-content">
                <?php foreach ($carImages as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" class="gallery-image" data-index="<?= $index ?>">
                <?php endforeach; ?>
            </div>
            <button class="gallery-prev" onclick="changeImage(-1)">❮</button>
            <button class="gallery-next" onclick="changeImage(1)">❯</button>
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
    <script>
    let currentIndex = 0;
    const images = document.querySelectorAll('.gallery-image');

    function openGallery(index) {
        const modal = document.getElementById('galleryModal');
        modal.style.display = 'flex';
        showImage(index);
    }

    function closeGallery() {
        const modal = document.getElementById('galleryModal');
        modal.style.display = 'none';
    }

    function showImage(index) {
        images.forEach(img => img.classList.remove('active'));
        currentIndex = index;
        images[currentIndex].classList.add('active');
    }

    function changeImage(direction) {
        currentIndex += direction;
        if (currentIndex < 0) {
            currentIndex = images.length - 1;
        } else if (currentIndex >= images.length) {
            currentIndex = 0;
        }
        showImage(currentIndex);
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeGallery();
        }
    });
</script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>