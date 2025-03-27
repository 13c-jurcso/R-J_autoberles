<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include "./db_connection.php";
include "./adatLekeres.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['berles_id'])) {
            // Egy adott bérlés lekérdezése
            $berles_id = (int)$_GET['berles_id'];
            $sql = "SELECT berlesek.berles_id, jarmuvek.gyarto, jarmuvek.tipus, felhasznalo.nev, berlesek.tol, berlesek.ig 
                    FROM berlesek 
                    INNER JOIN jarmuvek ON berlesek.jarmu_id = jarmuvek.jarmu_id 
                    INNER JOIN felhasznalo ON felhasznalo.felhasznalo_nev = berlesek.felhasznalo 
                    WHERE berlesek.berles_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $berles_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result) {
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Nincs ilyen bérlés']);
            }
        } else {
            // Összes bérlés lekérdezése
            $sql = "SELECT berlesek.berles_id, jarmuvek.gyarto, jarmuvek.tipus, felhasznalo.nev, berlesek.tol, berlesek.ig 
                    FROM berlesek 
                    INNER JOIN jarmuvek ON berlesek.jarmu_id = jarmuvek.jarmu_id 
                    INNER JOIN felhasznalo ON felhasznalo.felhasznalo_nev = berlesek.felhasznalo 
                    ORDER BY berlesek.berles_id DESC";
            $berlesek = adatokLekerese($sql);

            if (is_array($berlesek)) {
                echo json_encode(['status' => 'success', 'data' => $berlesek]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Nincs bérlés az adatbázisban']);
            }
        }
        break;

    case 'POST':
        // Új bérlés hozzáadása
        $input = json_decode(file_get_contents('php://input'), true);
        $jarmu_id = $input['jarmu_id'] ?? null;
        $felhasznalo = $input['felhasznalo'] ?? null;
        $tol = $input['tol'] ?? null;
        $ig = $input['ig'] ?? null;

        if (!$jarmu_id || !$felhasznalo || !$tol || !$ig) {
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó adatok']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO berlesek (jarmu_id, felhasznalo, tol, ig) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $jarmu_id, $felhasznalo, $tol, $ig);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Bérlés sikeresen hozzáadva']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hiba a hozzáadás során']);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Bérlés módosítása
        $input = json_decode(file_get_contents('php://input'), true);
        $berles_id = $input['berles_id'] ?? null;
        $jarmu_id = $input['jarmu_id'] ?? null;
        $felhasznalo = $input['felhasznalo'] ?? null;
        $tol = $input['tol'] ?? null;
        $ig = $input['ig'] ?? null;

        if (!$berles_id || !$jarmu_id || !$felhasznalo || !$tol || !$ig) {
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó adatok']);
            exit;
        }

        $stmt = $db->prepare("UPDATE berlesek SET jarmu_id = ?, felhasznalo = ?, tol = ?, ig = ? WHERE berles_id = ?");
        $stmt->bind_param("isssi", $jarmu_id, $felhasznalo, $tol, $ig, $berles_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Bérlés sikeresen módosítva']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hiba a módosítás során']);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // Bérlés törlése
        $input = json_decode(file_get_contents('php://input'), true);
        $berles_id = $input['berles_id'] ?? null;

        if (!$berles_id) {
            echo json_encode(['status' => 'error', 'message' => 'Hiányzó bérlés ID']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM berlesek WHERE berles_id = ?");
        $stmt->bind_param("i", $berles_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Sikeres törlés']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Hiba a törlés során']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Nem támogatott HTTP metódus']);
        break;
}

$db->close();
?>