<?php
include "./db_connection.php";
include "./adatLekeres.php";

session_start();
if ($_SESSION['admin'] == false) {
    $_SESSION['alert_message'] = "Kérem jelentkezzen be, hogy tovább tudjon lépni!";
    $_SESSION['alert_type'] = "warning";
    header("Location: ../../php/index.php");
    exit();
}

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
    <link rel="icon" href="../../admin_favicon.png" type="image/png">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <title>Jogosúltságok</title>
</head>
<body>
    <header>
        <div class="menu-toggle">☰ Menu</div>
        <nav>
            <ul>
                <li><a href="../../php/index.php">Főoldal</a></li>
                <li><a href="../../php/husegpontok.php">Hűségpontok</a></li>
                <li><a href="../../php/jarmuvek.php">Gépjárművek</a></li>
            </ul>
        </nav>
    </header>
    <h1>Jogosúltság</h1>

    <div class="menu">
        <a href="./autok_kezeles.php"><button type="submit" id="jarmuvek" onclick="mutatResz('resz1')">Járművek </button></a>
        <a href="./admin_jogosultsag.php"><button type="submit" id="jogosultsag" onclick="mutatResz('resz2')">Jogosultságok</button></a>
        <a href="./admin_berlesek.php"><button type="submit" id="berlesek">Bérlések</button></a>
        <a href="./admin_velemenyek.php"><button type="submit">Vélemények</button></a>
        <a href="./admin_akciok.php"><button type="submit">Akciók</button></a>
    </div>
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
                        echo '<td>
                                <button type="button" class="btn btn-danger torles_button" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmDeleteModal" 
                                    data-felhasznalo-id="' . $f['felhasznalo_nev'] . '">
                                        Törlés
                                </button>
                                </td></tr>';
                    }
                }
                else{
                    echo '<tr><td colspan="10">Nincs felhasználó rögzítve az adatbázisban.</td></tr>';
                }

                echo '</table>';
            ?>
        </div>
    </div>

    <footer class="container mt-5 mb-3 text-center text-muted">
        © <?= date('Y M') ?> R&J - Admin
    </footer>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- Cím módosítása -->
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Törlés Megerősítése</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tartalom módosítása -->
                    <p>Biztosan törölni szeretnéd ezt az elemet? Ez a művelet nem vonható vissza.</p>
                </div>
                <div class="modal-footer">
                    <!-- Gombok módosítása -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <!-- Adjunk a törlés gombnak egy ID-t, ha később JavaScripttel kezelnénk -->
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtnActual">Törlés</button>
                </div>
            </div>
        </div>
    </div>
    <form method="POST" id="deleteForm" style="display: none;">
        <input type="hidden" name="felhasznalo_nev" id="deleteFelhasznaloId">
        <button type="submit" name="delete_felhasznalo" id="submitDeleteButton"></button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedFelhasznaloId = null;

            // Minden törlés gomb eseménykezelése
            document.querySelectorAll('.torles_button').forEach(button => {
                button.addEventListener('click', function() {
                    selectedFelhasznaloId = this.getAttribute('data-felhasznalo-id');
                });
            });

            // A modálon belüli törlés gomb eseménykezelése
            document.getElementById('confirmDeleteBtnActual').addEventListener('click', function() {
                if (selectedFelhasznaloId) {
                    document.getElementById('deleteFelhasznaloId').value = selectedFelhasznaloId;
                    document.getElementById('submitDeleteButton').click();
                }
            });
        });
    </script>

</body>
</html>