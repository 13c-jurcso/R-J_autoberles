<?php
    include "./db_connection.php";
    include "./adatLekeres.php";


    session_start();

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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modositas</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/index.css">
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
    <div id="jarmuvek_modositas" class="tartalmi-resz">


        <form method="POST" enctype="multipart/form-data" class="form">
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
            <input type="text" name="leiras" value="<?= htmlspecialchars($car['leiras']) ?>"><br>

            <label for="ar">Ár:</label>
            <input type="number" name="ar" value="<?= htmlspecialchars($car['ar']) ?>"><br>

            <!-- <label for="kep_url">Képek:</label>
            <input type="file" name="kep_url[]" accept="image/*" multiple required><br> -->

            <button type="submit" name="update_vehicle">Mentés</button>
        </form>
    </div>
</body>
</html>