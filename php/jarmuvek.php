<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarmuvek</title>
    <link rel="stylesheet" href="../css/jarmuvek.css">
    <script defer src="../jarmuvek.js"></script>
</head>
<body>
<header>
    <div class="menu-toggle">☰ Menu</div>
    <nav>
        <ul>
            <li><a href="index.php">R&J</a></li>
            <li><a href="kapcsolat.php">Kapcsolat</a></li>
            <li><a href="husegpontok.php">Hűségpontok</a></li>
            <li><a href="jarmuvek.php">Gépjárművek</a></li>
            <?php if (isset($_SESSION['felhasznalo_nev'])): ?>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="card-container">
    <?php
        include './adatLekeres.php';
        $kocsi_kartya_sql = "SELECT jarmuvek.jarmu_id, jarmuvek.gyarto, jarmuvek.tipus, jarmuvek.gyartasi_ev, jarmuvek.motor, jarmuvek.leiras, jarmuvek.ar, jarmuvek.kep_url FROM jarmuvek;";
        $kocsiKartya = adatokLekerese($kocsi_kartya_sql);
        if(is_array($kocsiKartya)){
            foreach ($kocsiKartya as $kocsi) {
                echo '<div class="card">';
                    echo '<img src="' . $kocsi['kep_url'] . '" alt="' . $kocsi['gyarto'] . '" class="card-image">';
                    echo '<div class="card-content">';
                        echo '<h3 class="card-title">' . $kocsi['gyarto'] . '</h3>';
                        echo '<p class="card-text">' . $kocsi['tipus'] . '</p>';
                        echo '<p class="card-text">' . $kocsi['gyartasi_ev'] . '</p>';
                        echo '<p class="card-text">' . $kocsi['motor'] . '</p>';
                        echo '<p class="card-text">' . $kocsi['leiras'] . '</p>';
                        echo '<p class="card-text">' . $kocsi['ar'] .' Ft'. '</p>';
                    echo '</div>';
                    echo '<input class="berles-gomb" type="button" value="Bérlés" name="berles" id="' . $kocsi['jarmu_id'] . '" data-id="' . $kocsi['jarmu_id'] . '" data-gyarto="' . $kocsi['gyarto'] . '" data-típus="' . $kocsi['tipus'] . '" data-ev="' . $kocsi['gyartasi_ev'] . '" data-motor="' . $kocsi['motor'] . '" data-ar="' . $kocsi['ar'] . '" data-leiras="' . $kocsi['leiras'] . '" onclick="openModal(this)">';
                echo '</div>';
            } 
            echo '<div id="modal" class="modal">';
                echo '<div class="modal-content">';
                    echo '<span class="close" onclick="closeModal()">&times;</span>';
                    echo '<div id="modal-info"></div>';
                echo '</div>';
            echo '</div>';
            echo '<div id="overlay" class="overlay"></div>';
        }
        else{
            return $kocsiKartya;
        }
    ?>
</div>




</body>
</html>