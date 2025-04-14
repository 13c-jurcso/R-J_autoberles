<?php
session_start();

if (isset($_SESSION['alert_message'])) {
    include 'modal.php';
}
include './db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ellenőrizzük, hogy léteznek-e a szükséges adatok a POST tömbben
    if (isset($_POST['felhasznalo_nev']) && isset($_POST['jelszo'])) {
        $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
        $jelszo = $_POST['jelszo'];

        // Ellenőrizzük, hogy a felhasználónév létezik-e az adatbázisban
        $query = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $felhasznalo_nev);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Ellenőrizzük, hogy a jelszó helyes-e
            if (password_verify($jelszo, $user['jelszo'])) {
                $_SESSION['felhasznalo_nev'] = $felhasznalo_nev; // Bejelentkezés sikeres
                $_SESSION['admin'] = $user['admin'];
                header("Location: index.php"); // Átirányítás a főoldalra
                exit();
            } else {
                $_SESSION['alert_message'] = "Hibás jelszó!";
                $_SESSION['alert_type'] = "warning";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['alert_message'] =  "Nincs ilyen felhasználónév!";
            $_SESSION['alert_type'] = "warning";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['alert_message'] = "Kérlek, töltsd ki az összes mezőt!";
        $_SESSION['alert_type'] = "warning";
        header("Location: index.php");
        exit();
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];
    $jelszo = $_POST['jelszo'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jelszo_ujra = $_POST['jelszo_ujra'];

    if ($jelszo !== $jelszo_ujra) {
        echo "A két jelszó nem egyezik!";
        exit();
    }

    $query = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $felhasznalo_nev);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['alert_type'] = "Ez a felhasználónév már létezik!";
        $_SESSION['alert_type'] = "warning";
        header("Location: index.php");
        exit();
    } else {
        $hashed_jelszo = password_hash($jelszo, PASSWORD_DEFAULT);

        $insert_query = "INSERT INTO felhasznalo (felhasznalo_nev, nev, emailcim, jogositvany_kiallitasDatum, szamlazasi_cim, jelszo) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->bind_param("ssssss", $felhasznalo_nev, $nev, $emailcim, $jogositvany_kiallitasDatum, $szamlazasi_cim, $hashed_jelszo);
        if ($stmt->execute()) {
            $_SESSION['alert_type'] = "Sikeres regisztráció!";
            $_SESSION['alert_type'] = "warning";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['alert_type'] = "Hiba történt a regisztráció során: " . $stmt->error;
            $_SESSION['alert_type'] = "warning";
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J Autókölcsönző</title>
    <link rel="icon" href="../favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div id="torzs">
        <h1 class="cim">R&J autókölcsönző. Indulás!</h1>
        <div id="kezdo_input">
            <form action="jarmuvek.php" method="get">
                <label id="datuma">Átvétel dátuma</label>
                <input type="date" name="atvetel" required>
                <label id="datuma">Leadás dátuma</label>
                <input type="date" name="leadas" required>
                <input type="submit" value="Járművek megtekintése">
            </form>
        </div>
    </div>

    <div id="loginModal" class="modal">
        <div class="modal-content-login">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Bejelentkezés</h2>
            <form action="index.php" method="post">
                <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required>
                <input type="password" name="jelszo" placeholder="Jelszó" required>
                <input type="submit" value="Bejelentkezés">
            </form>
        </div>
    </div>

    <div id="registerModal" class="modal">
        <div class="modal-content-register">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Regisztráció</h2>
            <form action="register.php" method="post">
                <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required>
                <input type="text" name="nev" placeholder="Teljes név" required>
                <input type="email" name="emailcim" placeholder="Email" required>
                <input type="password" name="jelszo" placeholder="Jelszó" required>
                <input type="password" name="jelszo_ujra" placeholder="Jelszó újra" required>
                <input type="date" name="jogositvany_kiallitasDatum" placeholder="Jogosítvány érvényességi dátuma" required>
                <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required>
                <input type="submit" value="Regisztráció">
            </form>
        </div>
    </div>



    <!-- Meglévő kód -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../index.js"></script>
    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('header').classList.toggle('menu-opened');
        });
    </script>
    <footer>
        © <?= date('Y') ?> R&J - Minden jog fenntartva
    </footer>
</body>

</html>