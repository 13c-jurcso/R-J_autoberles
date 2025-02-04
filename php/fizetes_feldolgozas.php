<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['berles_id'])) {
    $berles_id = $_POST['berles_id'];

    // Adatbázis kapcsolat
    $conn = new mysqli("localhost", "root", "", "autoberles");
    if ($conn->connect_error) {
        die("Kapcsolódási hiba: " . $conn->connect_error);
    }

    // Fizetés megerősítése (kifizetve = 1)
    $sql = "UPDATE berlesek SET kifizetve = 1 WHERE berles_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $berles_id);

    if ($stmt->execute()) {
        echo "<script>alert('A fizetés sikeresen rögzítve!');</script>";
    } else {
        echo "<script>alert('Hiba történt a fizetés rögzítésekor: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}