<?php
// Az adatbázis kapcsolatot a db_connection.php-ből importáljuk
include './db_connection.php';

function adatokLekerese($muvelet) {
    global $db; // Használjuk az importált $db kapcsolatot

    // SQL lekérdezés futtatása
    $eredmeny = $db->query($muvelet);

    if ($db->errno == 0) {
        if ($eredmeny->num_rows > 0) {
            // Ha van találat, akkor visszatérünk az adatokkal
            $adatok = $eredmeny->fetch_all(MYSQLI_ASSOC);
            return $adatok;
        } else {
            return 'Nincsenek találatok!';
        }
    } else {
        return $db->error; // Ha hiba történt, visszatérünk a hibával
    }
}
?>
