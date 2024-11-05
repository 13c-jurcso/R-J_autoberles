<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapcsolat</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .contact-info {
            margin-bottom: 20px;
        }
        .contact-info p {
            margin: 5px 0;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input, textarea {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            border: none;
            background-color: #4CAF50;
            color: white;
            font-size: 1em;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
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
    <div class="container">
        <h1>Kapcsolat</h1>
        <p>Vegye fel velünk a kapcsolatot az alábbi elérhetőségek egyikén, vagy használja a kapcsolatfelvételi űrlapot.</p>

        <div class="contact-info">
            <h2>Elérhetőségeink</h2>
            <p><strong>Cím:</strong> 1234 Budapest, Fő utca 1.</p>
            <p><strong>Telefon:</strong> +36 1 234 5678</p>
            <p><strong>Email:</strong> info@nemletezokft.hu</p>
        </div>

        <h2>Kapcsolatfelvételi űrlap</h2>
        <form action="#" method="post">
            <label for="name">Név</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Üzenet</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Küldés</button>
        </form>
    </div>              

    <script>
        document.querySelector(".menu-toggle").addEventListener("click", function () {
            document.querySelector("header").classList.toggle("menu-opened");
        });
    </script>

</body>
</html>