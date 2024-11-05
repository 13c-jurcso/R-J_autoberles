<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $felhasznalo_nev = $_POST['felhasznalo_nev'];
    $jelszo = $_POST['jelszo'];

    $sql = "SELECT jelszo FROM felhasznalo WHERE felhasznalo_nev = '$felhasznalo_nev'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($jelszo, $row['jelszo'])) {
            $_SESSION['felhasznalo_nev'] = $felhasznalo_nev;
            echo "Sikeres bejelentkezés!";
            header("Location: index.php"); // Redirect to home page
            exit();
        } else {
            echo "Hibás jelszó!";
        }
    } else {
        echo "Nincs ilyen felhasználónév!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
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
        input[type="password"],
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="text"]:focus,
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

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Bejelentkezés</h2>
    <form action="login.php" method="post">
        <input type="text" name="felhasznalo_nev" placeholder="Felhasználónév" required><br>
        <input type="password" name="jelszo" placeholder="Jelszó" required><br>
        <button type="submit">Bejelentkezés</button>
    </form>
</body>
</html>
