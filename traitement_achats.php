<?php
require 'connect_db.php';
try{
    if(isset($_POST['client_id']) && isset($_POST['montant']) && isset($_POST['date_achat'])){
        $nom = $_POST['client_id'];
        $montant = $_POST['montant'];
        $date = $_POST['date_achat'];
        $sql = "INSERT INTO t_achats(client_id,montant,date_achat) values(?,?,?)";
        $requete = $connexion->prepare($sql);
        $requete->execute([$nom,$montant,$date]);
        header('location:achats.php');
    }
}catch(PDOException $e){
    echo "Erreur: " . $e->getMessage();
}
