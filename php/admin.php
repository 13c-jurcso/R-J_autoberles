<?php
include "./db_connection.php";
include "./adatLekeres.php";
// Jármű hozzáadása
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_vehicle'])) {
    $felhasznalas_id = $_POST['felhasznalas_id'];
    $szerviz_id = $_POST['szerviz_id'];
    $gyarto = $_POST['gyarto'];
    $tipus = $_POST['tipus'];
    $motor = $_POST['motor'];
    $gyartasi_ev = $_POST['gyartasi_ev'];
    $leiras = $_POST['leiras'];
    $ar = $_POST['ar'];
    $kep = $_FILES['kep_url'];
    $kepmappa ="./kepek/";
    $filenev = $kepmappa.basename($kep['name']);

    move_uploaded_file($kep["tmp_name"],$filenev);

    $modositas = $db->prepare("INSERT INTO jarmuvek (felhasznalas_id, szerviz_id, gyarto, tipus, motor, gyartasi_ev, leiras, ar, kep_url) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $modositas->bind_param("iisssssis", $felhasznalas_id, $szerviz_id, $gyarto, $tipus, $motor, $gyartasi_ev, $leiras, $ar, $filenev);

    if ($modositas->execute()) {
        echo '<div class="sikeres_hozzadas">Ez egy informatív üzenet!</div>';
    } else {
        echo "Hiba: " . $modositas->error;
    }
    $modositas->close();
}

// Jármű törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_vehicle'])) {
    $jarmu_id = $_POST['jarmu_id'];

    $torles = $db->prepare("DELETE FROM jarmuvek WHERE jarmu_id = ?");
    $torles->bind_param("i", $jarmu_id);

    if ($torles->execute()) {
        echo "Jármű sikeresen törölve!";
    } else {
        echo "Hiba: " . $torles->error;
    }
    $torles->close();
}

// Jármű lista lekérése
$jarmuvek = $db->query("SELECT * FROM jarmuvek");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vezérlőpult</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="husegpontok.php">Hűségpontok</a></li>
                <li><a href="jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Vezérlőpult</h1>
    <!-- Jármű hozzáadása -->
    <h2>Jármű hozzáadása</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="gyarto">Gyártó:</label>
        <input type="text" name="gyarto" required><br>
        
        <label for="tipus">Típus:</label>
        <input type="text" name="tipus" required><br>

        <label for="motor">Motor:</label>
        <input type="text" name="motor" required><br>

        <label for="felhasznalas_id">Felhasználási mód:</label>
        <select name="felhasznalas_id">
            <?php
                $felhasznalas_sql = "SELECT felhasznalas_id, felhasznalas.nev FROM felhasznalas;";
                $felhasznalas = adatokLekerese($felhasznalas_sql);
                if(is_array($felhasznalas)){
                    foreach ($felhasznalas as $f) {
                        echo '<option value="'. $f['felhasznalas_id'].'">' . $f['nev'] . '</option>'; 
                    }
                }
                else{
                    echo $felhasznalas;
                }
            ?>
        </select>

        <label for="szerviz_id">Szerviz ID:</label>
        <input type="number" name="szerviz_id" required><br>

        <label for="gyartasi_ev">Gyártási év:</label>
        <input type="date" name="gyartasi_ev" required><br>

        <label for="leiras">Leírás:</label>
        <input type="text" name="leiras" required><br>

        <label for="ar">Ár:</label>
        <input type="number" name="ar" required><br>
        <label for="kep_url">Kép:</label>
        <input type="file" name="kep_url" accept="image/*" required><br>

        <button type="submit" name="add_vehicle">Hozzáadás</button>
    </form>

    <!-- Jármű lista -->
    <h2>Járművek listája</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Felhasználás ID</th>
            <th>Szerviz ID</th>
            <th>Gyártó</th>
            <th>Típus</th>
            <th>Motor</th>
            <th>Gyártási év</th>
            <th>Leírás</th>
            <th>Ár</th>
            <th>Művelet</th>
        </tr>
        <?php if ($jarmuvek->num_rows > 0): ?>
            <?php while ($row = $jarmuvek->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['jarmu_id']; ?></td>
                    <td><?php echo $row['felhasznalas_id']; ?></td>
                    <td><?php echo $row['szerviz_id']; ?></td>
                    <td><?php echo $row['gyarto']; ?></td>
                    <td><?php echo $row['tipus']; ?></td>
                    <td><?php echo $row['motor']; ?></td>
                    <td><?php echo $row['gyartasi_ev']; ?></td>
                    <td><?php echo $row['leiras']; ?></td>
                    <td><?php echo $row['ar']; ?></td>
                    <td>
                        <form method="POST" action="" style="display:inline-block;">
                            <input type="hidden" name="jarmu_id" value="<?php echo $row['jarmu_id']; ?>">
                            <button type="submit" name="delete_vehicle">Törlés</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">Nincs jármű az adatbázisban.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$db->close();
?>
