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
    $carImages = json_decode($car['kep_url'], true); // JSON dekódolás tömbként
}

$queryReviews = "SELECT * FROM velemenyek WHERE jarmu_id = $carId ORDER BY datum DESC";
$resultReviews = $conn->query($queryReviews);
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($car) ? htmlspecialchars($car['gyarto'] . ' ' . $car['tipus']) : 'Autó részletek' ?> Részletek
    </title>
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
        <h1><?= htmlspecialchars($car['gyarto'] . ' ' . $car['tipus']) ?></h1>

        <div class="car-gallery">
            <?php if (!empty($carImages) && is_array($carImages)): ?>
            <div class="main-image">
                <img src="<?= htmlspecialchars($carImages[0]) ?>" class="main-car-image" alt="Fő autó kép"
                    onclick="openGallery(0)">
            </div>
            <div id="galleryModal" class="gallery-modal">
                <span class="close-gallery" onclick="closeGallery()">×</span>
                <div class="gallery-content">
                    <?php foreach ($carImages as $index => $image): ?>
                    <img src="<?= htmlspecialchars($image) ?>" class="gallery-image" data-index="<?= $index ?>"
                        alt="Galéria kép <?= $index + 1 ?>">
                    <?php endforeach; ?>
                </div>
                <button class="gallery-prev" onclick="changeImage(-1)" aria-label="Előző kép">❮</button>
                <button class="gallery-next" onclick="changeImage(1)" aria-label="Következő kép">❯</button>
            </div>
            <?php else: ?>
            <p class="text-muted">Nincs elérhető kép ehhez az autóhoz.</p>
            <?php endif; ?>
        </div>

        <p><strong>Motor:</strong> <?= htmlspecialchars($car['motor']) ?></p>
        <p><strong>Év:</strong> <?= htmlspecialchars($car['gyartasi_ev']) ?></p>
        <p><strong>Leírás:</strong> <?= nl2br(htmlspecialchars($car['leiras'])) ?></p>
        <p><strong>Ár:</strong> <?= number_format($car['ar'], 0, '.', ' ') ?> Ft</p>
        <hr>

        <h3>Vélemények</h3>
        <div class="reviews">
            <?php if ($resultReviews->num_rows > 0): ?>
            <?php while ($review = $resultReviews->fetch_assoc()): ?>
            <div class="review">
                <strong><?= htmlspecialchars($review['felhasznalo_nev']) ?>:</strong>
                <p><?= nl2br(htmlspecialchars($review['uzenet'])) ?></p>
                <small><?= htmlspecialchars($review['datum']) ?></small>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
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
    document.addEventListener('DOMContentLoaded', () => {
        const galleryModal = document.getElementById('galleryModal');
        const images = document.querySelectorAll('.gallery-image');
        const closeButton = document.querySelector('.close-gallery');
        const prevButton = document.querySelector('.gallery-prev');
        const nextButton = document.querySelector('.gallery-next');
        let currentIndex = 0;

        function openGallery(index) {
            if (images.length === 0) return;
            galleryModal.style.display = 'flex'; // Közvetlen display váltás
            currentIndex = index;
            showImage(currentIndex);
        }

        function closeGallery() {
            galleryModal.style.display = 'none';
        }

        function showImage(index) {
            images.forEach(img => {
                img.style.display = 'none'; // Minden képet elrejt
                img.classList.remove('active');
            });
            currentIndex = (index + images.length) % images.length; // Ciklikus index
            images[currentIndex].style.display = 'block'; // Csak az aktuális kép látható
            images[currentIndex].classList.add('active');
        }

        function changeImage(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = images.length - 1;
            if (currentIndex >= images.length) currentIndex = 0;
            showImage(currentIndex);
        }

        // Eseménykezelők
        document.querySelector('.main-car-image')?.addEventListener('click', () => openGallery(0));
        closeButton?.addEventListener('click', closeGallery);
        prevButton?.addEventListener('click', () => changeImage(-1));
        nextButton?.addEventListener('click', () => changeImage(1));

        // Billentyűzetvezérlés
        document.addEventListener('keydown', (event) => {
            if (galleryModal.style.display !== 'flex') return;
            switch (event.key) {
                case 'Escape':
                    closeGallery();
                    break;
                case 'ArrowLeft':
                    changeImage(-1);
                    break;
                case 'ArrowRight':
                    changeImage(1);
                    break;
            }
        });

        // Háttérre kattintás
        galleryModal?.addEventListener('click', (e) => {
            if (e.target === galleryModal) closeGallery();
        });
    });

    // Globális függvények
    window.openGallery = openGallery;
    window.closeGallery = closeGallery;
    window.changeImage = changeImage;
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('header').classList.toggle('menu-opened');
    });
    </script>
</body>

</html>

<?php
$conn->close();
?>