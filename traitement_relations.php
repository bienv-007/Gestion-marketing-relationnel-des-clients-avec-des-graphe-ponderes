<?php
require 'connect_db.php';

$message_erreur = "";

if(isset($_POST['client_parrain']) && isset($_POST['client_filleul'])){
    $parrain = $_POST['client_parrain'];
    $filleul = $_POST['client_filleul'];

    // 1. Vérification d'auto-parrainage
    if ($parrain == $filleul) {
        $message_erreur = "Un client ne peut pas se parrainer lui-même.";
    } else {
        // 2. Vérification si la relation EXACTE existe déjà (Doublon)
        $check_exists = $connexion->prepare("SELECT COUNT(*) FROM t_relations WHERE parrain_id = ? AND filleuil_id = ?");
        $check_exists->execute([$parrain, $filleul]);
        
        if ($check_exists->fetchColumn() > 0) {
            $message_erreur = "Cette relation existe déjà.";
        } else {
            // 3. Vérification de circularité (pour éviter les boucles infinies)
            $is_circular = false;
            $temp_parrain = $parrain;

            while ($temp_parrain != null) {
                $stmt = $connexion->prepare("SELECT parrain_id FROM t_relations WHERE filleuil_id = ?");
                $stmt->execute([$temp_parrain]);
                $parent = $stmt->fetchColumn();

                if ($parent == $filleul) {
                    $is_circular = true;
                    break;
                }
                $temp_parrain = $parent ? $parent : null;
            }

            if ($is_circular) {
                $message_erreur = "Erreur : Ce parrain est déjà un descendant de ce filleul (boucle circulaire).";
            } else {
                // 4. Tentative d'insertion avec gestion d'erreur SQL
                try {
                    $sql = "INSERT INTO t_relations(parrain_id, filleuil_id) VALUES (?, ?)";
                    $requete = $connexion->prepare($sql);
                    $requete->execute([$parrain, $filleul]);
                    header('location:relations.php');
                    exit();
                } catch (PDOException $e) {
                    // Capture l'erreur si le filleul a déjà un autre parrain différent
                    $message_erreur = "Ce filleul possède déjà un parrain.";
                }
            }
        }
    }
}

// Affichage de l'alerte et redirection
if (!empty($message_erreur)) {
    echo "<script>alert('" . addslashes($message_erreur) . "'); window.location.href='relations.php';</script>";
}
?>