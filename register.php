<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $_POST['felhasznalo_nev'];
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jelszo = password_hash($_POST['jelszo'], PASSWORD_BCRYPT); 

    $sql = "INSERT INTO felhasznalo (felhasznalo_nev, nev, emailcim, jogositvany_kiallitasDatum, szamlazasi_cim, husegpontok, jelszo)
            VALUES ('$felhasznalo_nev', '$nev', '$emailcim', '$jogositvany_kiallitasDatum', '$szamlazasi_cim', 0, '$jelszo')";

    if ($conn->query($sql) === TRUE) {
        echo "Sikeres regisztráció!";
        header("Location: index.php"); 
    } else {
        echo "Hiba: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Regisztráció</h2>
    <form action="register.php" method="post">
        <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
        <input type="text" name="nev" placeholder="Teljes név" required><br>
        <input type="email" name="emailcim" placeholder="Email cím" required><br>
        <input type="date" name="jogositvany_kiallitasDatum" placeholder="Jogosítvány kiállítás dátuma" required><br>
        <input type="text" name="szamlazasi_cim" placeholder="Számlázási cím" required><br>
        <input type="password" name="jelszo" placeholder="Jelszó" required><br>
        <button type="submit">Regisztráció</button>
        <a href="index.php">Vissza a főoldalra</a>
    </form>
</body>
</html>
