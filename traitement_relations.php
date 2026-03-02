<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'connect_db.php';

$message_erreur = "";
// On récupère l'action : 'add' (par défaut) ou 'delete'
$action = $_POST['action'] ?? 'add';

try {
    // --- ACTION : AJOUTER UNE RELATION ---
    if ($action === 'add' && isset($_POST['client_parrain'], $_POST['client_filleul'])) {
        $parrain = $_POST['client_parrain'];
        $filleul = $_POST['client_filleul'];

        if ($parrain == $filleul) {
            $message_erreur = "Un client ne peut pas se parrainer lui-même.";
        } else {
            // Vérification de doublon
            $check_exists = $connexion->prepare("SELECT COUNT(*) FROM t_relations WHERE parrain_id = ? AND filleuil_id = ?");
            $check_exists->execute([$parrain, $filleul]);
            
            if ($check_exists->fetchColumn() > 0) {
                $message_erreur = "Cette relation existe déjà.";
            } else {
                // Vérification de circularité
                $is_circular = false;
                $temp_parrain = $parrain;
                while ($temp_parrain != null) {
                    $stmt = $connexion->prepare("SELECT parrain_id FROM t_relations WHERE filleuil_id = ?");
                    $stmt->execute([$temp_parrain]);
                    $parent = $stmt->fetchColumn();
                    if ($parent == $filleul) { $is_circular = true; break; }
                    $temp_parrain = $parent ?: null;
                }

                if ($is_circular) {
                    $message_erreur = "Boucle détectée : ce parrain est déjà un descendant de ce filleul.";
                } else {
                    try {
                        $stmt = $connexion->prepare("INSERT INTO t_relations(parrain_id, filleuil_id) VALUES (?, ?)");
                        $stmt->execute([$parrain, $filleul]);
                        header('Location: relations.php');
                        exit();
                    } catch (PDOException $e) {
                        $message_erreur = "Ce filleul possède déjà un parrain.";
                    }
                }
            }
        }
    }

    // --- ACTION : SUPPRIMER UNE RELATION ---
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $connexion->prepare("DELETE FROM t_relations WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: relations.php');
            exit();
        } else {
            $message_erreur = "ID de relation manquant.";
        }
    }

} catch (PDOException $e) {
    $message_erreur = "Une erreur technique est survenue.";
}

// AFFICHAGE DE L'ERREUR (Style Premium)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-2xl border border-red-50 max-w-sm w-full text-center">
        <div class="text-red-500 mb-4 inline-block bg-red-50 p-4 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Action impossible</h2>
        <p class="text-gray-500 text-sm mb-8"><?= htmlspecialchars($message_erreur) ?></p>
        <a href="relations.php" class="block w-full bg-gray-900 text-white py-3 rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-lg shadow-gray-200">
            Retourner au réseau
        </a>
    </div>
</body>
</html>