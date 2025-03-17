<?php
// Globális adatbázis kapcsolat
$db = new mysqli('localhost', 'root', '', 'autoberles');

if ($db->connect_errno) {
    die("Csatlakozási hiba: " . $db->connect_error);
}

// SQL function lekérdezésekhez (opcionális, ha nem használod máshol)
function adatokLekerdezese($muvelet) {
    global $db; // A globális $db-t használjuk
    $eredmeny = $db->query($muvelet);
    if ($db->errno == 0) {
        if ($eredmeny->num_rows != 0) {
            return $eredmeny->fetch_all(MYSQLI_ASSOC);
        } else {
            return 'Nincs találat!';
        }
    }
    return $db->error;
}
?>