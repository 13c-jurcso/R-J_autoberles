<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jarmuvek</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        }

    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f0f0f0;
    }

    .card-container {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px;
    }

    .card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        width: 300px;
        overflow: hidden;
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: scale(1.05);
    }

    .card-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .card-content {
        padding: 16px;
    }

    .card-title {
        font-size: 1.5em;
        color: #333;
        margin-bottom: 8px;
    }

    .card-text {
        font-size: 1em;
        color: #666;
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
                <li><a href="logout.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<div class="card-container">
    <div class="card">
        <h3>Opel Astra G</h3>
         <?php
            
         ?>
        <input type="submit" value="Bérlés" name="berles">
    </div>
    
    <div class="card">
        <img src="https://via.placeholder.com/150" alt="Kép 2" class="card-image">
        <div class="card-content">
            <h3 class="card-title">Kártya címe 2</h3>
            <p class="card-text">Ez egy másik kártya példája.</p>
            <input type="submit" value="Bérlés" name="berles">
        </div>
    </div>

    <div class="card">
        <img src="https://via.placeholder.com/150" alt="Kép 3" class="card-image">
        <div class="card-content">
            <h3 class="card-title">Kártya címe 3</h3>
            <p class="card-text">Harmadik kártya leírása.</p>
            <input type="submit" value="Bérlés" name="berles">
        </div>
    </div>
</div>


<script>
    document.querySelector(".menu-toggle").addEventListener("click", function () {
        document.querySelector("header").classList.toggle("menu-opened");
    });
</script>

</body>
</html>