<?php
// Connexion à la base de données
require_once('connect_db.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $connexion->prepare("DELETE FROM t_relations WHERE id = ?");
    $resultat = $stmt->execute([$id]);

    if ($resultat) {
        // On renvoie un code 200 (Succès)
        http_response_code(200);
        echo "Supprimé avec succès";
    } else {
        // On renvoie un code 500 (Erreur serveur)
        http_response_code(500);
        echo "Erreur lors de la suppression";
    }
}
?>