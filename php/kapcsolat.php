<?php
// Munkamenet indítása
session_start();

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalo_nev'])) {
    // Ha nincs bejelentkezve, átirányítjuk a bejelentkezési oldalra
    echo '<script type="text/javascript">',
         'alert("Kérem jelentkezzen be, hogy tovább tudjon lépni!");',
         'window.location.href = "index.php";',
         '</script>';
    exit();
}

$conn = new mysqli("localhost", "root", "", "autoberles");

if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}

$query = "SELECT felhasznalo_nev, uzenet, datum FROM velemenyek ORDER BY datum DESC";
$result = $conn->query($query);

if (isset($_POST['submit_review'])) {
    // Felhasználónév kiolvasása a session-ből
    $username = $_SESSION['felhasznalo_nev'];
    // Üzenet biztonságos kezelése
    $message = $conn->real_escape_string($_POST['message']);

    // Beszúrás az adatbázisba
    $insertQuery = "INSERT INTO velemenyek (felhasznalo_nev, uzenet) VALUES ('$username', '$message')";
    if ($conn->query($insertQuery)) {
        $uzenet = "<p>Köszönjük a véleményt!</p>";
    } else {
        $uzenet = "<p>Hiba történt: " . $conn->error . "</p>";
    }

    // Átirányítás, hogy ne legyen újraküldés a formnak
    header("Location: kapcsolat.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapcsolat</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/kapcsolat.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="../index.js"></script>
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
                <li><a href="profilom.php">Profilom</a></li>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="#" onclick="openModal('loginModal')">Bejelentkezés</a></li>
                <li><a href="#" onclick="openModal('registerModal')">Regisztráció</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="container">
    <h1>Kapcsolat</h1>
    <p>Vegye fel velünk a kapcsolatot az alábbi elérhetőségek egyikén, vagy használja a kapcsolatfelvételi űrlapot.</p>

    <div class="contact-info">
        <h2>Elérhetőségeink</h2>
        <p><strong>Cím:</strong> 1234 Budapest, Fő utca 1.</p>
        <p><strong>Telefon:</strong> +36 1 234 5678</p>
        <p><strong>Email:</strong> info@nemletezokft.hu</p>
    </div>

    <h2>Kapcsolatfelvételi űrlap</h2>
    <form action="#" method="post">
        <label for="name">Név</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="message">Üzenet</label>
        <textarea id="message" name="message" rows="5" required></textarea>

        <button type="submit">Küldés</button>
    </form>
</div>

<div class="guestbook">
    <h2>Vendégkönyv</h2>
    <?php
    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            ?>
            <div class="review">
                <p><strong><?= htmlspecialchars($row['felhasznalo_nev']) ?>:</strong> <?= nl2br(htmlspecialchars($row['uzenet'])) ?></p>
                <small><?= htmlspecialchars($row['datum']) ?></small>
            </div>
        <?php
        endwhile;
    else:
        ?>
        <p>Még nincs vélemény.</p>
    <?php endif; ?>
</div>

<div class="guestbook">
    <form action="kapcsolat.php" method="post">
        <h3>Írja meg véleményét</h3>
        <label for="message">Üzenet</label>
        <textarea id="message" name="message" rows="5" required></textarea>
        <button type="submit" name="submit_review">Küldés</button>
    </form>
</div>

<?php
if (isset($uzenet)) {
    echo $uzenet;
}
?>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
</script>
</body>
</html>