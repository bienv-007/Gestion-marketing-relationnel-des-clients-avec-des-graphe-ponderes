<?php
require 'connect_db.php';

/**
 * Calcule la commission totale d'un client
 */
function calculerCommissions($connexion, $id_client) {
    $commission_totale = 0;

    // --- 1. CALCUL DES FILLEULS DIRECTS (5%) ---
    $sql_directs = "SELECT SUM(a.montant) as total_achats 
                    FROM t_achats a
                    JOIN t_relations r ON a.client_id = r.filleuil_id
                    WHERE r.parrain_id = :id_client";
    
    $stmt = $connexion->prepare($sql_directs);
    $stmt->execute(['id_client' => $id_client]);
    $result_direct = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result_direct['total_achats']) {
        $commission_totale += $result_direct['total_achats'] * 0.05;
    }

    // --- 2. CALCUL DES FILLEULS INDIRECTS (1%) ---
    $sql_indirects = "SELECT SUM(a.montant) as total_achats
                      FROM t_achats a
                      JOIN t_relations r1 ON a.client_id = r1.filleuil_id
                      JOIN t_relations r2 ON r1.parrain_id = r2.filleuil_id
                      WHERE r2.parrain_id = :id_client";
    
    $stmt = $connexion->prepare($sql_indirects);
    $stmt->execute(['id_client' => $id_client]);
    $result_indirect = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result_indirect['total_achats']) {
        $commission_totale += $result_indirect['total_achats'] * 0.01;
    }

    return $commission_totale;
}

// Initialisation des variables pour l'affichage détaillé
$resultat_commission = null;
$nom_client_choisi = "";
$total_achats_perso = 0;
$filleuls_directs = [];
$filleuls_indirects = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['client_id'])) {
    $id_selectionne = $_POST['client_id'];
    
    // 1. Calcul de la commission
    $resultat_commission = calculerCommissions($connexion, $id_selectionne);
    
    // 2. Récupération du nom
    $stmt_nom = $connexion->prepare("SELECT nom FROM t_clients WHERE id = ?");
    $stmt_nom->execute([$id_selectionne]);
    $nom_client_choisi = $stmt_nom->fetchColumn();

    // 3. Somme des achats personnels
    $stmt_achats = $connexion->prepare("SELECT SUM(montant) FROM t_achats WHERE client_id = ?");
    $stmt_achats->execute([$id_selectionne]);
    $total_achats_perso = $stmt_achats->fetchColumn() ?: 0;

    // 4. Liste des filleuls directs (Niveau 1)
    $stmt_fd = $connexion->prepare("SELECT nom FROM t_clients c JOIN t_relations r ON c.id = r.filleuil_id WHERE r.parrain_id = ?");
    $stmt_fd->execute([$id_selectionne]);
    $filleuls_directs = $stmt_fd->fetchAll(PDO::FETCH_COLUMN);

    // 5. Liste des filleuls indirects (Niveau 2)
    $stmt_fi = $connexion->prepare("SELECT c.nom FROM t_clients c 
                                    JOIN t_relations r1 ON c.id = r1.filleuil_id 
                                    JOIN t_relations r2 ON r1.parrain_id = r2.filleuil_id 
                                    WHERE r2.parrain_id = ?");
    $stmt_fi->execute([$id_selectionne]);
    $filleuls_indirects = $stmt_fi->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissions - MLM System</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f8fafc; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>

<header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-600 p-2 rounded-lg text-white">
                    <i data-lucide="network" class="w-6 h-6"></i>
                </div>
                <span class="font-bold text-xl tracking-tight text-gray-900 hidden md:block">
                    Gestion<span class="text-indigo-600"> MLM</span>
                </span>
            </div>
            <ul class="flex items-center gap-1 md:gap-4 font-medium text-gray-600">
                <?php
                $menu_items = [
                    'index.php' => ['Tableau de bord', 'layout-dashboard'],
                    'clients.php' => ['Clients', 'users'],
                    'relations.php' => ['Relations', 'git-graph'],
                    'achats.php' => ['Achats', 'shopping-cart'],
                    'commission.php' => ['Commissions', 'wallet']
                ];
                foreach ($menu_items as $url => $info) :
                    $isActive = ($current_page == $url);
                    $activeClass = $isActive ? 'text-indigo-600 bg-indigo-50 border-b-2 border-indigo-600' : 'hover:text-indigo-600 hover:bg-gray-50 border-b-2 border-transparent';
                ?>
                    <li>
                        <a href="<?= $url ?>" class="flex items-center gap-2 px-3 py-5 transition-all duration-200 <?= $activeClass ?>">
                            <i data-lucide="<?= $info[1] ?>" class="w-5 h-5"></i>
                            <span class="hidden lg:inline text-[14px]"><?= $info[0] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</header>

<main class="max-w-6xl mx-auto px-4 py-10">
    <div class=" mb-10">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Analyse des Commissions</h1>
        <p class="text-gray-500 mt-2 font-medium">Visualisez les performances et les gains de votre réseau</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-5 space-y-6">
            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                        <i data-lucide="calculator" class="w-5 h-5"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">Calculateur de Commissions</h2>
                </div>

                <form method="post" class="space-y-4">
                    <div>
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest ml-1">Sélectionner un membre</label>
                        <select name="client_id" class="mt-1 block w-full border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50/50 transition-all" required>
                            <option value="">-- Choisir un client --</option>
                            <?php
                                $requete = $connexion->query("SELECT * FROM t_clients ORDER BY nom");
                                while($c = $requete->fetch()){
                                    $sel = (isset($id_selectionne) && $id_selectionne == $c['id']) ? 'selected' : '';
                                    echo '<option value="'.$c['id'].'" '.$sel.'>'.$c['nom'].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gray-900 text-white p-3.5 font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Calculer les Commissions
                    </button>
                </form>

                <?php if ($resultat_commission !== null): ?>
                    <hr class="my-8 border-gray-100">
                    
                    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
                        <div class="p-5 bg-indigo-600 rounded-2xl text-center shadow-md shadow-indigo-100">
                            <span class="text-indigo-100 text-xs font-medium uppercase tracking-widest">Commission Totale</span>
                            <p class="text-3xl font-black text-white mt-1"><?= number_format($resultat_commission, 2) ?> $</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 stat-card">
                                <span class="text-gray-400 text-[11px] font-bold uppercase tracking-wider">Total Achats</span>
                                <p class="text-lg font-bold text-gray-800 mt-1"><?= number_format($total_achats_perso, 2) ?> $</p>
                            </div>

                            <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 stat-card">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-blue-700 text-[11px] font-bold uppercase tracking-wider">Niveau 1 (Directs)</span>
                                    <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full"><?= count($filleuls_directs) ?></span>
                                </div>
                                <p class="text-xs text-blue-600 leading-relaxed">
                                    <?= !empty($filleuls_directs) ? implode(' • ', array_map('htmlspecialchars', $filleuls_directs)) : 'Aucun parrainage direct' ?>
                                </p>
                            </div>

                            <div class="p-4 bg-purple-50/50 rounded-xl border border-purple-100 stat-card">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-purple-700 text-[11px] font-bold uppercase tracking-wider">Niveau 2 (Indirects)</span>
                                    <span class="bg-purple-100 text-purple-700 text-[10px] px-2 py-0.5 rounded-full"><?= count($filleuls_indirects) ?></span>
                                </div>
                                <p class="text-xs text-purple-600 leading-relaxed">
                                    <?= !empty($filleuls_indirects) ? implode(' • ', array_map('htmlspecialchars', $filleuls_indirects)) : 'Aucun parrainage indirect' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="lg:col-span-7">
            <section class="bg-white shadow-sm rounded-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="trophy" class="w-5 h-5 text-yellow-500"></i>
                        Classement des Revenus
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-[10px] uppercase tracking-[0.2em] border-b border-gray-100">
                                <th class="px-6 py-4 font-bold">Rang</th>
                                <th class="px-6 py-4 font-bold">Membre</th>
                                <th class="px-6 py-4 font-bold text-right">Commissions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php 
                            $sql = "SELECT id, nom FROM t_clients";
                            $requete = $connexion->query($sql);
                            $liste_triee = [];

                            while($client = $requete->fetch()){
                                $montant = calculerCommissions($connexion, $client['id']);
                                $liste_triee[] = ['nom' => $client['nom'], 'commission' => $montant];
                            }

                            usort($liste_triee, function($a, $b) {
                                return $b['commission'] <=> $a['commission'];
                            });

                            $rang = 1;
                            foreach($liste_triee as $item){
                                $isTop3 = ($rang <= 3);
                                $rowStyle = $isTop3 ? 'bg-yellow-50/40' : 'hover:bg-gray-50';
                                $rankIcon = '';
                                if($rang == 1) $rankIcon = '🥇';
                                elseif($rang == 2) $rankIcon = '🥈';
                                elseif($rang == 3) $rankIcon = '🥉';
                                
                                echo '<tr class="transition-colors '.$rowStyle.'">
                                        <td class="px-6 py-4">
                                            <span class="text-sm font-bold '.($isTop3 ? 'text-yellow-600' : 'text-gray-400').'">
                                                '.($rankIcon ?: '#'.$rang).'
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-white border-2 '.($isTop3 ? 'border-yellow-200' : 'border-gray-100').' flex items-center justify-center text-[10px] font-bold text-gray-600 shadow-sm">
                                                    '.strtoupper(substr($item['nom'], 0, 1)).'
                                                </div>
                                                <span class="text-sm font-semibold text-gray-700">'.htmlspecialchars($item['nom']).'</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-black '.($item['commission'] > 0 ? 'text-emerald-600' : 'text-gray-300').'">
                                                '.number_format($item['commission'], 2, ',', ' ').' $
                                            </span>
                                        </td>
                                      </tr>';
                                $rang++;
                            }

                            if (empty($liste_triee)) {
                                echo '<tr><td colspan="3" class="p-10 text-center text-gray-400">Aucune donnée disponible</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>