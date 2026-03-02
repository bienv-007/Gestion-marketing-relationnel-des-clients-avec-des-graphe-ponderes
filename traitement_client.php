<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'connect_db.php';

$message_erreur = "";
// On récupère l'action : 'add' (par défaut), 'update' ou 'delete'
$action = $_POST['action'] ?? 'add';

try {
    // --- ACTION : AJOUTER ---
    if ($action === 'add') {
        $nom = trim($_POST['nom'] ?? '');
        if (!empty($nom)) {
            // Vérification doublon
            $check = $connexion->prepare("SELECT COUNT(*) FROM t_clients WHERE nom = ?");
            $check->execute([$nom]);
            if ($check->fetchColumn() > 0) {
                $message_erreur = "Ce client existe déjà.";
            } else {
                $stmt = $connexion->prepare("INSERT INTO t_clients(nom) VALUES (?)");
                $stmt->execute([$nom]);
                header('Location: clients.php');
                exit();
            }
        } else {
            $message_erreur = "Le nom est obligatoire.";
        }
    }

    // --- ACTION : MODIFIER ---
    elseif ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $nom = trim($_POST['nom'] ?? '');

        if ($id && !empty($nom)) {
            $stmt = $connexion->prepare("UPDATE t_clients SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);
            header('Location: clients.php');
            exit();
        } else {
            $message_erreur = "Données invalides pour la modification.";
        }
    }

    // --- ACTION : SUPPRIMER ---
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;

        if ($id) {
            $stmt = $connexion->prepare("DELETE FROM t_clients WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: clients.php');
            exit();
        }
    }

} catch (PDOException $e) {
    $message_erreur = "Une erreur technique est survenue.";
}

// Si on arrive ici, c'est qu'il y a eu une erreur (pas de redirection)
// On peut stocker l'erreur en session pour l'afficher sur clients.php ou l'afficher ici
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-xl border border-red-100 max-w-sm w-full text-center">
        <div class="text-red-500 mb-4 inline-block bg-red-50 p-4 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Oups !</h2>
        <p class="text-gray-500 text-sm mb-6"><?= htmlspecialchars($message_erreur) ?></p>
        <a href="clients.php" class="inline-block bg-gray-900 text-white px-6 py-2 rounded-xl font-semibold hover:bg-gray-800 transition-all">
            Retourner aux clients
        </a>
    </div>
</body>
</html>