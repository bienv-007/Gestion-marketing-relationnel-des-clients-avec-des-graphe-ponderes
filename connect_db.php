<?php
try {
    //code...
    $connexion = new PDO ("mysql:host=sql100.infinityfree.com;dbname=if0_41292500_db_mlm","if0_41292500","KWYtUCsgjtyM");
} catch (PDOException $e) {
    echo "erreur :".$e->getMessage();
}
