<?php
    function adatokLekerese($muvelet){
        
        $db = new mysqli($servername, $username, $password, $dbname);
        if($db->connect_errno == 0){
            $eredmeny = $db->query($muvelet);
            if($db->errno ==0){
                if($eredmeny->num_rows !=0){
                    
                    $adatok = $eredmeny->fetch_all(MYSQLI_ASSOC);
                    return $adatok;
                }
                else{
                    return 'Nincsenek találatok!';
                }
            }
            else{
                return $db->error;
            }
        }
        
        else{
            return $db->connect_error;
        }
    }
?>