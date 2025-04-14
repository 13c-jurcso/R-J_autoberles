<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['felhasznalo_nev'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$felhasznalo_nev = $_SESSION['felhasznalo_nev'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch rental data
    $sql = "SELECT b.berles_id, b.tol, b.ig, b.kifizetve, j.gyarto, j.ar, j.tipus, j.motor,
            (DATEDIFF(b.ig, b.tol) + 1) * j.ar AS osszeg
            FROM berlesek AS b
            JOIN jarmuvek AS j ON b.jarmu_id = j.jarmu_id
            WHERE b.felhasznalo = ?
            ORDER BY b.tol DESC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $felhasznalo_nev);
    $stmt->execute();
    $result = $stmt->get_result();

    $rentals = [];
    while ($row = $result->fetch_assoc()) {
        $rentals[] = $row;
    }

    echo json_encode($rentals);
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}

$db->close();
?>
