<?php
include "./db_connection.php";
include "./adatLekeres.php";

// Jármű lista lekérése
$jarmuvek = $db->query("SELECT * FROM jarmuvek");
$felhasznalok = $db->query("SELECT * FROM felhasznalo;");
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
    
    <div class="menu">
        <button id="jarmuvek" onclick="mutatResz('resz1')">Járművek <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
            <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z"/>
            </svg>
        </button>
        <button id="jogosultsag" onclick="mutatResz('resz2')">Jogosultságok <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill-down" viewBox="0 0 16 16">
            <path d="M12.5 9a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7m.354 5.854 1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V10.5a.5.5 0 0 0-1 0v2.793l-.646-.647a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
            <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4"/>
            </svg>
        </button>
    </div>

    <!-- Járművek szerkeztése aloldal -->
    <div id="resz1" class="tartalmi-resz">
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

        <!-- Jármű törlése -->
        <h2>Járművek Törlése</h2>
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

    </div>
    <!-- Jogosúltságok aloldal -->
    <div id="resz2" class="tartalmi-resz">
        <h2>Jogosultság módosítása</h2>
        <table border="1">
            <tr>
                <th>Felhasználó Név</th>
                <th>Teljes Név</th>
                <th>Email Cím</th>
                <th>Jogosítvány ki.dátuma</th>
                <th>Számlázási Cím</th>
                <th>Hűségpontok</th>
                <th>Admin</th>
            </tr>
            <?php if ($felhasznalok->num_rows > 0): ?>
                <?php while ($row = $felhasznalok->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['felhasznalo_nev']; ?></td>
                        <td><?php echo $row['nev']; ?></td>
                        <td><?php echo $row['emailcim']; ?></td>
                        <td><?php echo $row['jogositvany_kiallitasDatum']; ?></td>
                        <td><?php echo $row['szamlazasi_cim']; ?></td>
                        <td><?php echo $row['husegpontok']; ?></td>
                        <td><?php echo $row['admin']; ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline-block;">
                                <input type="hidden" name="felhasznalo_nev" value="<?php echo $row['felhasznalo_nev']; ?>">
                                <button type="submit" name="felhasznalo_modositas">Modosítás</button>
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
        
    </div>
    <div>
    <?php
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
                        echo '<div class="sikeres" id="animDiv">Sikeres hozzáadás!</div>';
                    } else {
                        echo '<div class="sikertelen" id="animDiv">Hiba a törlés során!</div>';
                        var_dump($torles->error);
                    }
                    $modositas->close();
                }

                // Jármű törlése
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_vehicle'])) {
                    $jarmu_id = $_POST['jarmu_id'];

                    $torles = $db->prepare("DELETE FROM jarmuvek WHERE jarmu_id = ?");
                    $torles->bind_param("i", $jarmu_id);

                    if ($torles->execute()) {
                        echo '<div class="sikeres" id="animDiv">Sikeres törlés!</div>';
                    } else {
                        echo '<div class="sikertelen" id="animDiv">Hiba a törlés során!</div>';
                        var_dump($torles->error);
                    }
                    $torles->close();
                }
            ?>
    </div>

    <script>
        function mutatResz(reszAzonosito, gomb) {
            // Az összes tartalmi rész rejtése
            const tartalmiReszek = document.querySelectorAll('.tartalmi-resz');
            tartalmiReszek.forEach(resz => resz.classList.remove('aktiv'));

            // Csak az adott rész megjelenítése
            const aktivResz = document.getElementById(reszAzonosito);
            if (aktivResz) {
                aktivResz.classList.add('aktiv');
            }

            // Az összes gombról eltávolítjuk az "aktiv" osztályt
            const gombok = document.querySelectorAll('.menu button');
            gombok.forEach(g => g.classList.remove('aktiv'));

            // Az aktuális gombhoz hozzáadjuk az "aktiv" osztályt
            gomb.classList.add('aktiv');
        }

        document.getElementById("animDiv").addEventListener("click", function() {
            this.classList.add("hidden");
        });
  </script>
</body>
</html>

<?php
$db->close();
?>
