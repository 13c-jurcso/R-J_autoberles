<?php
session_start();
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&J - Autóbérlés</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="index.css">

    <style>
        /* Modális ablakok stílusa */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .modal h2 {
            text-align: center;
        }

        .modal input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .modal button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal button:hover {
            background-color: #0056b3;
        }

        /* Gombok stílusa */
        .btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Menü stílusok */
        header {
            background-color: #333;
            color: white;
            padding: 10px;
        }

        header nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: space-around;
        }

        header nav ul li {
            display: inline;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
        }

        header nav ul li a:hover {
            background-color: #555;
            border-radius: 5px;
        }
    </style>
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
                <li><a href="profilom.php">Profilom</a></li>
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="#" onclick="document.getElementById('loginModal').style.display='flex'">Bejelentkezés</a></li>
                <li><a href="#" onclick="document.getElementById('registerModal').style.display='flex'">Regisztráció</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main>
    <div id="torzs">
        <h1>R&J autókölcsönző. Indulás!</h1>
        <div id="kezdo_input">
            <form action="jarmuvek.php" method="get">
                <select name="hely" id="hely">
                    <option value="Veszprém">Veszprém</option>
                    <option value="Budapest">Budapest</option>
                    <option value="Debrecen">Debrecen</option>
                </select>
                <input type="datetime-local" name="atvetel" id="atvetel" required>
                <input type="datetime-local" name="leadas" id="leadas" required>
                <input type="submit" id="submit_jarmuvek" value="Járművek megtekintése">
            </form>
        </div>
    </div>
</main>

<!-- Bejelentkezési modális -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('loginModal').style.display='none'">&times;</span>
        <form action="login.php" method="post">
            <h2>Bejelentkezés</h2>
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
            <input type="password" name="jelszo" placeholder="Jelszó" required><br>
            <button type="submit" class="btn">Belépés</button>
        </form>
    </div>
</div>

<!-- Regisztrációs modális -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('registerModal').style.display='none'">&times;</span>
        <form action="register.php" method="post">
            <h2>Regisztráció</h2>
            <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
            <input type="text" name="nev" placeholder="Teljes név" required><br>
            <input type="email" name="emailcim" placeholder="Email cím" required><br>
            <input type="date" name="jogositvany_kiallitasDatum" required><br>
            <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required><br>
            <input type="password" name="jelszo" placeholder="Jelszó" required><br>
            <input type="password" name="jelszo_ujra" placeholder="Jelszó újra" required><br>
            <button type="submit" class="btn">Regisztráció</button>
        </form>
    </div>
</div>

<script>
    // Menü toggle
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });

    // Modális ablakok bezárása
    window.onclick = function(event) {
        if (event.target.className === "modal") {
            event.target.style.display = "none";
        }
    }
</script>

</body>
</html>
