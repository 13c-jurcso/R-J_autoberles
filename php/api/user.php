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
    // Fetch user data
    $sql = "SELECT nev, emailcim, szamlazasi_cim, jogositvany_kiallitasDatum, husegpontok, admin FROM felhasznalo WHERE felhasznalo_nev = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $felhasznalo_nev);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user data
    $nev = $_POST['nev'];
    $emailcim = $_POST['emailcim'];
    $szamlazasi_cim = $_POST['szamlazasi_cim'];
    $jogositvany_kiallitasDatum = $_POST['jogositvany_kiallitasDatum'];

    $update_sql = "UPDATE felhasznalo SET nev = ?, emailcim = ?, szamlazasi_cim = ?, jogositvany_kiallitasDatum = ? WHERE felhasznalo_nev = ?";
    $stmt = $db->prepare($update_sql);
    $stmt->bind_param("sssss", $nev, $emailcim, $szamlazasi_cim, $jogositvany_kiallitasDatum, $felhasznalo_nev);

    if ($stmt->execute()) {
        // Handle password update if provided
        if (!empty($_POST['uj_jelszo']) && !empty($_POST['uj_jelszo_megerosites'])) {
            $uj_jelszo = $_POST['uj_jelszo'];
            $uj_jelszo_megerosites = $_POST['uj_jelszo_megerosites'];

            if ($uj_jelszo === $uj_jelszo_megerosites) {
                $hashed_password = password_hash($uj_jelszo, PASSWORD_DEFAULT);
                $update_password_sql = "UPDATE felhasznalo SET jelszo = ? WHERE felhasznalo_nev = ?";
                $stmt_password = $db->prepare($update_password_sql);
                $stmt_password->bind_param("ss", $hashed_password, $felhasznalo_nev);
                $stmt_password->execute();
                $stmt_password->close();
            } else {
                echo json_encode(["success" => false, "message" => "Passwords do not match"]);
                exit();
            }
        }

        echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating profile: " . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}

$db->close();
?>
