<?php
    include "./db_connection.php";
    include "./adatLekeres.php";


    session_start();

    // Modal include
    if (isset($_SESSION['alert_message'])) {
        include 'modal.php';
    }

    $conn = new mysqli("localhost", "root", "", "autoberles");

    if ($conn->connect_error) {
        die("Csatlakozási hiba: " . $conn->connect_error);
    }

    $kocsiId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $autok_lekerdezes = "SELECT * FROM jarmuvek WHERE jarmu_id = $kocsiId";
    $autok = $conn->query($autok_lekerdezes);


    if ($autok->num_rows > 0) {
        $car = $autok->fetch_assoc();
    }

    // Jármű Módosítása
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vehicle'])) {
        $jarmu_id = $_POST['jarmu_id'];
        $felhasznalas_id = $_POST['felhasznalas_id'];
        $szerviz_id = $_POST['szerviz_id'];
        $gyarto = $_POST['gyarto'];
        $tipus = $_POST['tipus'];
        $motor = $_POST['motor'];
        $gyartasi_ev = $_POST['gyartasi_ev'];
        $leiras = $_POST['leiras'];
        $ar = $_POST['ar'];

        $modositas = $db->prepare("UPDATE jarmuvek SET felhasznalas_id = ?, szerviz_id = ?, gyarto = ?, tipus = ?, motor = ?, gyartasi_ev = ?, leiras = ?, ar = ? WHERE jarmu_id = ?");
        $modositas->bind_param("iisssssii", $felhasznalas_id, $szerviz_id, $gyarto, $tipus, $motor, $gyartasi_ev, $leiras, $ar, $jarmu_id);

        if ($modositas->execute()) {
            $_SESSION['uzenet'] = '<div class="sikeres">Sikeres módosítás!</div>';
        } else {
            echo '<div class="sikertelen" id="animDiv">Hiba a módosítás során!</div>';
            var_dump($modositas->error);
        }
        $modositas->close();
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modositas</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="./php/index.php">Főoldal</a></li>
                <li><a href="./php/husegpontok.php">Hűségpontok</a></li>
                <li><a href="./php/jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Módosítás</h1>
    <hr>

    <div>
        <!-- Üzenetek -->
        <?php
            // session_start();
            if (isset($_SESSION['uzenet'])) {
                echo $_SESSION['uzenet'];
                unset($_SESSION['uzenet']);
            }
        ?>
    </div>

    <div class="menu">
        <a href="./autok_kezeles.php"><button>Vissza a járművekhez</button></a>
    </div>

    <div id="jarmuvek_modositas" class="tartalmi-resz">


        <form method="POST" enctype="multipart/form-data" class="form">

            <input type="hidden" name="jarmu_id" value="<?= htmlspecialchars($car['jarmu_id']) ?>">

            <label for="gyarto">Gyártó:</label>
            <input type="text" name="gyarto" value="<?= htmlspecialchars($car['gyarto']) ?>"><br>

            <label for="tipus">Típus:</label>
            <input type="text" name="tipus" value="<?= htmlspecialchars($car['tipus']) ?>"><br>

            <label for="motor">Motor:</label>
            <input type="text" name="motor" value="<?= htmlspecialchars($car['motor']) ?>"><br>

            <label for="felhasznalas_id">Felhasználási mód:</label>
            <select name="felhasznalas_id">
                <?php
                    $felhasznalas_sql = "SELECT felhasznalas.felhasznalas_id, felhasznalas.nev FROM felhasznalas;";
                    $felhasznalas = adatokLekerese($felhasznalas_sql);
                    if (is_array($felhasznalas)) {
                        foreach ($felhasznalas as $f) {
                            echo '<option value="'. $f['felhasznalas_id'].'">' . $f['nev'] . '</option>'; 
                        }
                    } else {
                        echo $felhasznalas;
                    }
                ?>
            </select>

            <label for="szerviz_id">Szerviz ID:</label>
            <input type="number" name="szerviz_id" value="<?= htmlspecialchars($car['szerviz_id']) ?>"><br>

            <label for="gyartasi_ev">Gyártási év:</label>
            <input type="date" name="gyartasi_ev" value="<?= htmlspecialchars($car['gyartasi_ev']) ?>"><br>

            <label for="leiras">Leírás:</label>
            <textarea name="leiras"><?= htmlspecialchars($car['leiras']) ?></textarea><br>

            <label for="ar">Ár:</label>
            <input type="number" name="ar" value="<?= htmlspecialchars($car['ar']) ?>"><br>

            <!-- <label for="kep_url">Képek:</label>
            <input type="file" name="kep_url[]" accept="image/*" multiple required><br> -->

            <button type="submit" name="update_vehicle">Mentés</button>
        </form>
    </div>
</body>
</html>