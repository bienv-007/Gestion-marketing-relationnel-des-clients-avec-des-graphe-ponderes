<?php
try {
    //code...
    $connexion = new PDO ("mysql:host=localhost;dbname=db_mlm","root","");
} catch (PDOException $e) {
    echo "erreur :".$e->getMessage();
}
