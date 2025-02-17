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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
        <button id="berlesek" onclick="mutatResz('resz3')">Bérlések <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
            </svg>
        </button>
        
        
    </div>

    <!-- Járművek szerkeztése aloldal -->
    <div id="resz1" class="tartalmi-resz">
        <!-- Jármű hozzáadása -->
        <h2>Jármű hozzáadása</h2>
        <form method="POST" enctype="multipart/form-data" class="form">
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
                            <form method="POST" action="">
                                <input type="hidden" name="jarmu_id" value="<?php echo $row['jarmu_id']; ?>">
                                <input type="button" class="torles_button" onclick="openModal()" value="Törlés">
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
        <form method="POST" class="form"> 
            <label>Regisztrált emberek:</label>
            <select name="felhasznalo_nev">
                <option>-- Kérem válasszon --</option>
                <?php
                    $felhasznalok_sql = "SELECT * FROM felhasznalo;";
                    $felhasznalok = adatokLekerese($felhasznalok_sql);
                    if(is_array($felhasznalok)){
                        foreach ($felhasznalok as $f) {
                            echo '<option value="'. $f['felhasznalo_nev'].'">' . $f['nev'] . '</option>'; 
                        }
                    }
                    else{
                        echo $felhasznalok;
                    }
                ?>
            </select>
            <label for="admin">Admin jogosultság:</label>
            <select id="admin" name="admin">
                <option>-- Kérem válasszon --</option>
                <option value="1">Admin</option>
                <option value="0">Normál felhasználó</option>
            </select>
            
            <button type="submit" name="felhasznalo_modositas">Mentés</button>
        </form>
    </div>

    <!--Bérlé<sek-->
    <div id="resz3" class="tartalmi-resz">
        <h2>Bérlések</h2>
        <?php
            $berlesek_listazasa_sql = "SELECT berlesek.berles_id, jarmuvek.gyarto, jarmuvek.tipus, felhasznalo.nev, berlesek.tol, berlesek.ig FROM berlesek 
                                       INNER JOIN jarmuvek ON berlesek.jarmu_id=jarmuvek.jarmu_id INNER JOIN felhasznalo ON felhasznalo.felhasznalo_nev=berlesek.felhasznalo;";
            $berlesek_listazasa = adatokLekerese($berlesek_listazasa_sql);
            echo '<table><tr><th>Bérlés sorszáma</th><th>Kibérelt jármű gyártója</th><th>Kibérelt jármű típusa</th><th>Bérlő neve</th><th>Átvétel időpontja</th><th>Leadás dátuma</th><th>Művelet</th></tr>';
            if(is_array($berlesek_listazasa)){
                foreach ($berlesek_listazasa as $b) {
                    echo '<tr><td>' . $b['berles_id'] . '</td>';
                    echo '<td>' . $b['gyarto'] . '</td>';
                    echo '<td>' . $b['tipus'] . '</td>';
                    echo '<td>' . $b['nev'] . '</td>';
                    echo '<td>' . $b['tol'] . '</td>';
                    echo '<td>' . $b['ig'] . '</td>';
                    echo '<td><form method="POST">
                                <input type="hidden" name="berles_id" value="' . $b['berles_id'] . '">
                                <input name="delete_berles" class="torles_button" data-toggle="modal" type="button" value="Törlés">
                          </form></td></tr>';
                }
            }
            else{
                echo '<tr><td colspan="10">Nincs bérlés rögzítve az adatbázisban.</td></tr>';
            }

            echo '</table>';
        ?>
    </div>

    <!-- Modális ablak -->
    <div id="csoo" class="modal">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="icon-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="red" style="margin-top: 12px" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                        </svg>
                    </div>
                    <h4 class="modal-title w-100">Figyelem!</h4>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <h3>Biztos benne, högy törölni kívánja az elemet?</h3>
                    <p>Ez a művelet nem visszavonható.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModal()">Mégse</button>
                    <form method="POST">
                        <button type="submit" class="btn btn-danger" name="delete_vehicle">Törlés</button>
                    </form>
                </div>
            </div>
        </div>
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

            // Bérlés törlése
            if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['']))

            //Felhasználó jogosultságának módosítása:
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['felhasznalo_modositas'])) {
                $felhasznalo_nev = $_POST['felhasznalo_nev'];
                $admin = (int)$_POST['admin'];
            
                // Adatbázis frissítése
                $stmt = $db->prepare("UPDATE felhasznalo SET admin = ? WHERE felhasznalo_nev = ?");
                $stmt->bind_param("is", $admin, $felhasznalo_nev);
            
                if ($stmt->execute()) {
                    echo '<div id="animDiv">Jogosultság sikeresen módosítva.</div>';
                } else {
                    echo '<div id="animDiv">Hiba!.</div>';
                }
            }
        ?>
    </div>
    
    

    <div id="overlay" class="overlay"></div>

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

        function openModal() {
            // document.getElementById('berles_id').value = button.getAttribute('data-id');
            document.getElementById('csoo').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
            console.log("asd");
        }

        function closeModal() {
            document.getElementById('csoo').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
  </script>
</body>
</html>

<?php
$db->close();
?>
