<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ellenőrizzük, hogy léteznek-e a szükséges adatok a POST tömbben
    if (isset($_POST['felhasznalo_nev']) && isset($_POST['jelszo'])) {
        $felhasznalo_nev = $db->real_escape_string($_POST['felhasznalo_nev']);
        $jelszo = $_POST['jelszo'];

        // Ellenőrizzük, hogy a felhasználónév létezik-e az adatbázisban
        $query = "SELECT * FROM felhasznalo WHERE felhasznalo_nev = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $felhasznalo_nev);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Ellenőrizzük, hogy a jelszó helyes-e
            if (password_verify($jelszo, $user['jelszo'])) {
                $_SESSION['felhasznalo_nev'] = $felhasznalo_nev; // Bejelentkezés sikeres
                header("Location: index.php"); // Átirányítás a főoldalra
                exit();
            } else {
                echo "Hibás jelszó!";
            }
        } else {
            echo "Nincs ilyen felhasználónév!";
        }
    } else {
        echo "Kérlek, töltsd ki az összes mezőt!";
    }
}
?>
