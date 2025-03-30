<?php
include "./db_connection.php";
include "./adatLekeres.php";

session_start();

// Felhasználó törlése
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_felhasznalo'])) {
    $fnev = $_POST['felhasznalo_nev'];

    $torles = $db->prepare("DELETE FROM `felhasznalo` WHERE felhasznalo_nev = ?;");
    $torles->bind_param("s", $fnev);

    if ($torles->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">
                                    Sikeres törlés!
                                </div>';
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">
                                    Hiba a törlés során!
                                </div>';
        var_dump($torles->error);
    }
    $torles->close();
}

//Felhasználó jogosultságának módosítása:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['felhasznalo_modositas'])) {
    $felhasznalo_nev = $_POST['felhasznalo_nev'];
    $admin = (int)$_POST['admin'];

    // Adatbázis frissítése
    $stmt = $db->prepare("UPDATE felhasznalo SET admin = ? WHERE felhasznalo_nev = ?");
    $stmt->bind_param("is", $admin, $felhasznalo_nev);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="alert alert-success" role="alert">
                                    Jogosultság sikeres módosítva!
                                </div>';
    } else {
        $_SESSION['uzenet'] = '<div class="alert alert-danger" role="alert">
                                    Hiba történt a módosítás során!
                                </div>';
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Jogosúltságok</title>
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
    <h1>Jogosúltság</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button id="jarmuvek" onclick="mutatResz('resz1')">Járművek <svg xmlns="http://www.w3.org/2000/svg" width="16"
                height="16" fill="currentColor" class="bi bi-car-front-fill" viewBox="0 0 16 16">
                <path
                    d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679q.05.242.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.8.8 0 0 0 .381-.404l.792-1.848ZM3 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2m10 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2M6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2zM2.906 5.189a.51.51 0 0 0 .497.731c.91-.073 3.35-.17 4.597-.17s3.688.097 4.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 11.691 3H4.309a.5.5 0 0 0-.447.276L2.906 5.19Z" />
            </svg>
        </button></a>
        <a href="./admin_jogosultsag.php"><button id="jogosultsag" onclick="mutatResz('resz2')">Jogosultságok <svg xmlns="http://www.w3.org/2000/svg"
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
        <a href="./admin_velemenyek.php"><button>Vélemények</button></a>
        <a href="./admin_akciok.php"><button>Akciók</button></a>
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

    <div class="regisztralas">
        <h2>Felhasználó Regisztrálás</h2>
        <form method="POST" class="form">
            <label>Felhasználónév</label>
            <input type="text" name="username">

            <label>Teljes Név</label>
            <input type="text" name="name">

            <label>Email</label>
            <input type="email" name="email">

            <label>Jelszó</label>
            <input type="password" name="jelszo">

            <label>Jelszó újra</label>
            <input type="password" name="jelszo_ujra">

            <label>Számlázási cím</label>
            <input type="text" name="szamlazasi_cim">

            <button type="submit" name="regisztralas">Regisztrálás</button>
        </form>
    </div>
    <hr>

    <!-- Jogosúltságok aloldal -->
    <div class="jogosultsagok">
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
    <hr>
    <div class="jogosultsagok">
        <h2>Törlés</h2>
        <div class="table-container">
            <?php
                $felhasznalok_listazasa_sql = "SELECT felhasznalo.felhasznalo_nev, felhasznalo.nev, felhasznalo.emailcim, felhasznalo.szamlazasi_cim, 
                                                felhasznalo.husegpontok, felhasznalo.admin FROM felhasznalo;";
                $felhasznalok_listazasa = adatokLekerese($felhasznalok_listazasa_sql);
                echo '<table><tr><th>Felhasználónév</th><th>Teljsen név</th><th>Email</th><th>Számlázási cím</th><th>Hűségpontok</th><th>Admin</th><th>Művelet</th></tr>';
                if(is_array($felhasznalok_listazasa)){
                    foreach ($felhasznalok_listazasa as $f) {
                        echo '<tr><td>' . $f['felhasznalo_nev'] . '</td>';
                        echo '<td>' . $f['nev'] . '</td>';
                        echo '<td>' . $f['emailcim'] . '</td>';
                        echo '<td>' . $f['szamlazasi_cim'] . '</td>';
                        echo '<td>' . $f['husegpontok'] . '</td>';
                        echo '<td>' . $f['admin'] . '</td>';
                        echo '<td><form method="post">
                                    <input type="hidden" name="felhasznalo_nev" value="' . $f['felhasznalo_nev'] . '">
                                    <button type="submit" name="delete_felhasznalo" class="torles_button">Törlés</button>
                            </form></td></tr>';
                    }
                }
                else{
                    echo '<tr><td colspan="10">Nincs felhasználó rögzítve az adatbázisban.</td></tr>';
                }

                echo '</table>';
            ?>
        </div>
    </div>

    <div>
        <?php
            
        ?>
    </div>
</body>
</html>