<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="index.css">
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
<form>
        <div id="torzs">
          <h1>R&J autókölcsönző. Indulás!</h1>
          <div id="kezdo_input">
          <select name="hely" id="hely">
            <option value="hely">Veszprém</option>
            <option value="hely">Budapest</option>
            <option value="hely">Debrecen</option>
          </select>
          <input type="datetime-local" name="atvetel" id="atvetel">
          <input type="datetime-local" name="leadas" id="leadas">
          <input type="submit" id="submit_jarmuvek" value="Járművek megtekintése">
          </div>
        </div>
      </form>


<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
    const ido = new Date();
        let felvetel = Date.parse(document.getElementById("felvetel").value);
        let felvetel_ido = new Date(felvetel);
        if (felvetel < ido.getTime() || isNaN(felvetel)) return alert("A felvételi idő nem lehet korábban a jelenlegi időnél!");
        let leadas = Date.parse(document.getElementById("leadas").value);
        let leadas_ido = new Date(leadas);
        if (leadas < felvetel || isNaN(leadas)) return alert("A leadási idő nem lehet hamarabb, mint a felvételi idő!");
</script>

</body>
</html>