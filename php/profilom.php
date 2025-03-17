<?php
session_start();
include './db_connection.php';

// Ha a felhasználó nincs bejelentkezve, irányítsuk át a bejelentkező oldalra
if (!isset($_SESSION['felhasznalo_nev'])) {
    header("Location: login.php");
    exit();
}

// A bejelentkezett felhasználó neve
$felhasznalo_nev = $_SESSION['felhasznalo_nev'];

// SQL lekérdezés a felhasználó adatainak lekérdezésére
$sql = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = '$felhasznalo_nev'";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<p>Nincs ilyen felhasználó!</p>";
    exit();
}

// SQL lekérdezés a felhasználó bérléseinek lekérdezésére a járművekkel együtt
$berlesek_sql = "SELECT b.berles_id, b.tol, b.ig, b.kifizetve, j.gyarto, j.ar, j.tipus, j.motor, 
                (DATEDIFF(b.ig, b.tol) + 1) * j.ar AS osszeg 
                FROM berlesek AS b JOIN jarmuvek AS j ON b.jarmu_id = j.jarmu_id 
                WHERE b.felhasznalo = '$felhasznalo_nev';";
$berlesek_result = $db->query($berlesek_sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilom</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<!-- Navbar -->
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

<!-- Profil oldal tartalma -->
<div class="container">
    <form>
        <h2>Profilom</h2>
        <p><strong>Felhasználónév:</strong> <?php echo htmlspecialchars($user['felhasznalo_nev']); ?></p>
        <p><strong>Név:</strong> <?php echo htmlspecialchars($user['nev']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['emailcim']); ?></p>
        <p><strong>Számlázási cím:</strong> <?php echo htmlspecialchars($user['szamlazasi_cim']); ?></p>
        <p><strong>Jogosítvány kiállítás dátuma:</strong> <?php echo htmlspecialchars($user['jogositvany_kiallitasDatum']); ?></p>
        <p><strong>Hűségpontjaim:</strong> <?php echo htmlspecialchars($user['husegpontok']); ?></p>
    </form>
</div>
<div style="text-align: center">
    <?php
        if ($user['admin'] == 1) {
            echo '<a href="/berles/admin/php/autok_kezeles.php"><button class="back-btn">Vezérlőpult</button></a>';
        }
    ?>
    <a href="modosit_profil.php"><button class="back-btn">Profil módosítása</button></a>
</div>
<div class="tablazat">
    <h3 style="text-align: center">Bérelt autóim</h3>
    <?php if ($berlesek_result->num_rows > 0): ?>
        <table class="table table-success table-striped">
            <thead>
                <tr>
                    <th>Gyártó</th>
                    <th>Típus</th>
                    <th>Motor</th>
                    <th>Napi Ár (Ft)</th>
                    <th>Bérlés kezdete</th>
                    <th>Bérlés vége</th>
                    <th>Teljes Összeg (Ft)</th>
                    <th>Kifizetve</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($berles = $berlesek_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($berles['gyarto']); ?></td>
                        <td><?php echo htmlspecialchars($berles['tipus']); ?></td>
                        <td><?php echo htmlspecialchars($berles['motor']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($berles['ar'], 0, ',', ' ')); ?></td>
                        <td><?php echo htmlspecialchars($berles['tol']); ?></td>
                        <td><?php echo htmlspecialchars($berles['ig']); ?></td>
                        <td><?php echo htmlspecialchars(number_format($berles['osszeg'], 0, ',', ' ')); ?></td>
                        <td><?php echo $berles['kifizetve'] ? 'Igen' : 'Nem'; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nincs bérelt autó.</p>
    <?php endif; ?>

</div>

<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
</script>

</body>
</html>
