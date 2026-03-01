<?php
require 'connect_db.php';

// On récupère l'action (par défaut 'add' si non précisée)
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if ($action == 'add') {
    $client_id = $_POST['client_id'];
    $montant = $_POST['montant'];
    $date_achat = $_POST['date_achat'];

    $sql = "INSERT INTO t_achats (client_id, montant, date_achat) VALUES (?, ?, ?)";
    $stmt = $connexion->prepare($sql);
    $stmt->execute([$client_id, $montant, $date_achat]);
} 
elseif ($action == 'update') {
    $id = $_POST['id'];
    $client_id = $_POST['client_id'];
    $montant = $_POST['montant'];
    $date_achat = $_POST['date_achat'];

    $sql = "UPDATE t_achats SET client_id = ?, montant = ?, date_achat = ? WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->execute([$client_id, $montant, $date_achat, $id]);
} 
elseif ($action == 'delete') {
    $id = $_POST['id'];

    $sql = "DELETE FROM t_achats WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->execute([$id]);
}

// Redirection vers la page des achats
header('Location: achats.php');
exit();
?>