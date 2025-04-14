<?php
session_start();

// Modal include
if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['felhasznalo_nev'])) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "autoberles");

if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}

// Vélemények lekérdezése
$query = "SELECT felhasznalo_nev, uzenet, datum FROM velemenyek ORDER BY datum DESC";
$result = $conn->query($query);

// Form submission handling
if (isset($_POST['submit'])) {
    $username = $_SESSION['felhasznalo_nev'];
    $message = $conn->real_escape_string($_POST['message']);

    $insertQuery = "INSERT INTO velemenyek (felhasznalo_nev, uzenet, jarmu_id) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ss", $username, $message);

    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Köszönjük a véleményt!";
        $_SESSION['alert_type'] = "success";
    } else {
        $_SESSION['alert_message'] = "Hiba történt: " . $stmt->error;
        $_SESSION['alert_type'] = "warning";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J - Kapcsolat</title>
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/kapcsolat.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="../node_modules/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="../index.js"></script>
</head>

<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">R&J</a></li>
                <li><a href="kapcsolat.php">Kapcsolat</a></li>
                <li><a href="jarmuvek.php">Bérlés</a></li>
                <li><a href="forum.php">Gépjárművek</a></li>
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
        <h1>Kapcsolat</h1>
        <p>Vegye fel velünk a kapcsolatot az alábbi elérhetőségek egyikén, vagy használja a kapcsolatfelvételi űrlapot.</p>

        <div class="contact-info">
            <h2>Elérhetőségeink</h2>
            <p><strong>Cím:</strong> 1234 Budapest, Fő utca 1.</p>
            <p><strong>Telefon:</strong> +36 1 234 5678</p>
            <p><strong>Email:</strong> info@nemletezokft.hu</p>
        </div>

        <h1>Kapcsolatfelvételi űrlap</h1>
        <form action="kapcsolat.php" method="post">
            <label for="name">Név</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['felhasznalo_nev']); ?>" readonly>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Üzenet</label>
            <textarea id="message" name="message" rows="5" maxlength="200" required oninput="updateCharCount()"></textarea>
            <div id="charCount">0/200</div>

            <button type="submit" name="submit">Küldés</button>
        </form>
    </div>
    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.querySelector(".menu-toggle").addEventListener("click", function() {
            document.querySelector("header").classList.toggle("menu-opened");
        });

        function updateCharCount() {
            const textarea = document.getElementById("message");
            const charCount = document.getElementById("charCount");
            const currentLength = textarea.value.length;
            charCount.textContent = `${currentLength}/200`;
        }
    </script>
</body>

</html>