<?php

 
$conn = new mysqli("localhost", "root", "", "autoberles");


if ($conn->connect_error) {
    die("Csatlakozási hiba: " . $conn->connect_error);
}


$query = "SELECT felhasznalo_nev, uzenet, datum FROM velemenyek ORDER BY datum DESC";
$result = $conn->query($query);


if (isset($_POST['submit_review'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $message = $conn->real_escape_string($_POST['message']);

    $insertQuery = "INSERT INTO velemenyek (felhasznalo_nev, uzenet) VALUES ('$username', '$message')";
    if ($conn->query($insertQuery)) {
        $uzenet="<p>Köszönjük a véleményt!</p>";
    } else {
        $uzenet= "<p>Hiba történt: " . $conn->error . "</p>";
    }
    

    header("Location: kapcsolat.php");
   exit();
    
  
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kapcsolat</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin-top: 15;
            padding: 4;
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
        .guestbook {
    margin-top: 20px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 5px;
    justify-content:center;
    margin: 0 auto;
    text-align: center;
    width:80%;
}
#guestbook {
    margin-top: 20px;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 5px;
    justify-content:center;
    margin: 0 auto;
    text-align: center;
    width:80%;
}
.review {
    margin-bottom: 15px;
    padding: 10px;
    border-bottom: 1px solid #ccc;
}
.review p {
    margin: 0;
}
.review small {
    display: block;
    color: #888;
    margin-top: 5px;
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
   


<div class="guestbook">
     <h2>Vendégkönyv</h2>
    <?php
    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
    ?>
            <div class="review">
                <p><strong><?= htmlspecialchars($row['felhasznalo_nev']) ?>:</strong> <?= nl2br(htmlspecialchars($row['uzenet'])) ?></p>
                <small><?= htmlspecialchars($row['datum']) ?></small>
            </div>
    <?php
        endwhile;
    else:
        ?>
        <p>Még nincs vélemény.</p>
    <?php endif; ?>


    </div>
    <div class="guestbook">
<form action="kapcsolat.php"  method="post">
    <h3>Írja meg véleményét</h3>
    <label for="username">Felhasználónév</label>
    <input type="text" id="username" name="username" required>

    <label for="message">Üzenet</label>
    <textarea id="message" name="message" rows="5" required></textarea>

    <button type="submit" name="submit_review">Küldés</button>
</form>
</div>
 <?php
if (isset($uzenet)) {
    echo $uzenet;
}

?>


    <script>
        document.querySelector(".menu-toggle").addEventListener("click", function () {
            document.querySelector("header").classList.toggle("menu-opened");
        });
    </script>

</body>
</html>