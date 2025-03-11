<?php
include "./db_connection.php";
include "./adatLekeres.php";

session_start();

// Akció hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_akcio'])) {
    $jarmu_id = $_POST['jarmu_id'];
    $kedvezmeny_szazalek = $_POST['kedvezmeny_szazalek'];
    $kezdete = $_POST['kezdete'];
    $vege = $_POST['vege'];
    $leiras = $_POST['leiras'];
    $is_black_friday = isset($_POST['is_black_friday']) ? 1 : 0;

    $stmt = $db->prepare("INSERT INTO akciok (jarmu_id, kedvezmeny_szazalek, kezdete, vege, leiras, is_black_friday) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssi", $jarmu_id, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Akció sikeresen hozzáadva!</div>';
    } else {
        $_SESSION['uzenet'] = '<div class="sikertelen" id="animDiv">Hiba az akció hozzáadása során!</div>';
    }
    $stmt->close();
}

// Black Friday akció hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_black_friday'])) {
    $jarmu_id = $_POST['jarmu_id'];
    $kedvezmeny_szazalek = 50; // Alapértelmezett nagy kedvezmény Black Friday-re
    $kezdete = date('Y-11-28'); // Példa: Black Friday 2025-ben
    $vege = date('Y-11-30');
    $leiras = "Black Friday különleges ajánlat!";
    $is_black_friday = 1;

    $stmt = $db->prepare("INSERT INTO akciok (jarmu_id, kedvezmeny_szazalek, kezdete, vege, leiras, is_black_friday) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idsssi", $jarmu_id, $kedvezmeny_szazalek, $kezdete, $vege, $leiras, $is_black_friday);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Black Friday akció sikeresen hozzáadva!</div>';
    } else {
        $_SESSION['uzenet'] = '<div class="sikertelen" id="animDiv">Hiba a Black Friday akció hozzáadása során!</div>';
    }
    $stmt->close();
}

// Akció törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_akcio'])) {
    $akcio_id = $_POST['akcio_id'];

    $stmt = $db->prepare("DELETE FROM akciok WHERE akcio_id = ?");
    $stmt->bind_param("i", $akcio_id);

    if ($stmt->execute()) {
        $_SESSION['uzenet'] = '<div class="sikeres" id="animDiv">Akció sikeresen törölve!</div>';
    } else {
        $_SESSION['uzenet'] = '<div class="sikertelen" id="animDiv">Hiba az akció törlése során!</div>';
    }
    $stmt->close();
}

// Járművek lekérdezése az űrlapokhoz
$jarmuvek_sql = "SELECT jarmu_id, gyarto, tipus FROM jarmuvek";
$jarmuvek = adatokLekerese($jarmuvek_sql);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akciók kezelése</title>
    <link rel="stylesheet" href="../css/admincss/admin.css">
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
    <h1>Akciók kezelése</h1>

    <div class="menu">
        <a href="./admin.php"><button>Járművek</button></a>
        <a href="./admin_jogosultsag.php"><button>Jogosultságok</button></a>
        <a href="./admin_berlesek.php"><button>Bérlések</button></a>
        <a href="./admin_velemenyek.php"><button>Vélemények</button></a>
        <a href="./admin_akciok.php"><button>Akciók</button></a>
    </div>

    <div>
        <?php
        if (isset($_SESSION['uzenet'])) {
            echo $_SESSION['uzenet'];
            unset($_SESSION['uzenet']);
        }
        ?>
    </div>

    <div class="tartalmi-resz">
        <h2>Új akció hozzáadása</h2>
        <form method="POST" class="form">
            <label>Jármű:</label>
            <select name="jarmu_id" required>
                <?php
                if (is_array($jarmuvek) && !empty($jarmuvek)) {
                    foreach ($jarmuvek as $j) {
                        echo '<option value="' . $j['jarmu_id'] . '">' . $j['gyarto'] . ' ' . $j['tipus'] . '</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }
                ?>
            </select>

            <label>Kedvezmény (%):</label>
            <input type="number" name="kedvezmeny_szazalek" min="1" max="100" required>

            <label>Kezdete:</label>
            <input type="date" name="kezdete" required>

            <label>Vége:</label>
            <input type="date" name="vege" required>

            <label>Leírás:</label>
            <input type="text" name="leiras">

            <label><input type="checkbox" name="is_black_friday"> Black Friday akció</label>

            <button type="submit" name="add_akcio">Hozzáadás</button>
        </form>

        <h2>Black Friday akció gyors hozzáadása</h2>
        <form method="POST" class="form">
            <label>Jármű:</label>
            <select name="jarmu_id" required>
                <?php
                if (is_array($jarmuvek) && !empty($jarmuvek)) {
                    foreach ($jarmuvek as $j) {
                        echo '<option value="' . $j['jarmu_id'] . '">' . $j['gyarto'] . ' ' . $j['tipus'] . '</option>';
                    }
                } else {
                    echo '<option value="">Nincsenek elérhető járművek</option>';
                }
                ?>
            </select>
            <button type="submit" name="add_black_friday" class="black-friday-btn">Black Friday akció (50%)</button>
        </form>
    </div>

    <div class="table-container">
        <h2>Aktuális akciók</h2>
        <?php
        $akciok_sql = "SELECT a.akcio_id, j.gyarto, j.tipus, a.kedvezmeny_szazalek, a.kezdete, a.vege, a.leiras, a.is_black_friday 
                       FROM akciok a 
                       INNER JOIN jarmuvek j ON a.jarmu_id = j.jarmu_id";
        $akciok = adatokLekerese($akciok_sql);

        echo '<table><tr><th>ID</th><th>Jármű</th><th>Kedvezmény (%)</th><th>Kezdete</th><th>Vége</th><th>Leírás</th><th>Black Friday</th><th>Művelet</th></tr>';
        if (is_array($akciok) && !empty($akciok)) {
            foreach ($akciok as $a) {
                echo '<tr>';
                echo '<td>' . $a['akcio_id'] . '</td>';
                echo '<td>' . $a['gyarto'] . ' ' . $a['tipus'] . '</td>';
                echo '<td>' . $a['kedvezmeny_szazalek'] . '</td>';
                echo '<td>' . $a['kezdete'] . '</td>';
                echo '<td>' . $a['vege'] . '</td>';
                echo '<td>' . ($a['leiras'] ?? 'Nincs') . '</td>';
                echo '<td>' . ($a['is_black_friday'] ? 'Igen' : 'Nem') . '</td>';
                echo '<td><form method="POST">
                            <input type="hidden" name="akcio_id" value="' . $a['akcio_id'] . '">
                            <button type="submit" name="delete_akcio" class="torles_button">Törlés</button>
                          </form></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">Nincs akció az adatbázisban.</td></tr>';
        }
        echo '</table>';
        ?>
    </div>

    <script>
        document.getElementById("animDiv")?.addEventListener("click", function() {
            this.classList.add("hidden");
        });
    </script>
</body>
</html>
<?php $db->close(); ?>