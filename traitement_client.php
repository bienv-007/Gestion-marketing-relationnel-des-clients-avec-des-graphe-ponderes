<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'connect_db.php';

// Variable pour stocker un éventuel message d'erreur
$message_erreur = "";

if (isset($_POST['nom'])) {
    // 1. Nettoyage de la donnée (sécurité supplémentaire)
    $nom = trim($_POST['nom']);

    if (!empty($nom)) {
        // 2. Vérification si le client existe déjà
        $check = $connexion->prepare("SELECT COUNT(*) FROM t_clients WHERE nom = ?");
        $check->execute([$nom]);
        $existe = $check->fetchColumn();

        if ($existe > 0) {
            $message_erreur = "Ce client existe déjà dans la base de données.";
        } else {
            // 3. Insertion si tout est bon
            try {
                $sql = "INSERT INTO t_clients(nom) VALUES (?)";
                $requete = $connexion->prepare($sql);
                $requete->execute([$nom]);
                
                header('Location: clients.php');
                exit(); // Toujours mettre exit après un header location
            } catch (PDOException $e) {
                $message_erreur = "Une erreur est survenue lors de l'enregistrement.";
            }
        }
    } else {
        $message_erreur = "Le nom ne peut pas être vide.";
    }
}
?>

<?php if ($message_erreur): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($message_erreur) ?>
    </div>
<?php endif; ?>