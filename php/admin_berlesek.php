<?php
    include "./db_connection.php";
    include "./adatLekeres.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="stylesheet" href="../css/index.css"> -->
    <link rel="stylesheet" href="../css/admincss/admin_berlesek.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Bérlések</title>
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
    <h1>Bérlések</h1>

    <div class="aloldalak">
        <a href="./admin.php"><button id="jarmuvek">Járművek <svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                <path
                    d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
            </svg>
        </button></a>
        <a href="./admin_jogosultsag.php"><button id="jogosultsag">Jogosultságok <svg xmlns="http://www.w3.org/2000/svg"
                width="16" height="16" fill="currentColor" class="bi bi-person-fill-down" viewBox="0 0 16 16">
                <path
                    d="M12.5 9a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7m.354 5.854 1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V10.5a.5.5 0 0 0-1 0v2.793l-.646-.647a.5.5 0 0 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                <path
                    d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
            </svg>
        </button></a>
        <a href="./admin_berlesek.php"><button id="berlesek">Bérlések <svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                <path
                    d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
                <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
            </svg>
        </button></a>
    </div>
    <hr>
    <div>
        <!-- Üzenetek -->
        <?php
            // session_start();
            if (isset($_SESSION['uzenet'])) {
                echo $_SESSION['uzenet'];
            }
        ?>
    </div>

    <h2>Törlés</h2>
    <div class="table-container">
        
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
                    echo '<td><form method="DELETE">
                                <input type="hidden" name="berles_id" value="' . $b['berles_id'] . '">
                                <input name="delete_berles" class="torles_button" type="submit" value="Törlés">
                          </form></td></tr>';
                }
            }
            else{
                echo '<tr><td colspan="10">Nincs bérlés rögzítve az adatbázisban.</td></tr>';
            }

            echo '</table>';
        ?>
    </div>
    <div>
        <?php
            // Bérlés törlése
            if($_SERVER['REQUEST_METHOD'] == "DELETE" && isset($_POST['delete_berles'])){
                $berles_id = $_POST['berles_id'];

                //berles törlése
                $stmt = $db->prepare("DELETE FROM berlesek WHERE berlesek.berles_id = ?");
                $stmt->bind_param("i", [$berles_id]);
                var_dump($berles_id);
                if($stmt->execute()){
                    session_start();
                    $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Sikeres hozzáadás!</div>';
                }

            }
        ?>
    </div>
</body>
</html>