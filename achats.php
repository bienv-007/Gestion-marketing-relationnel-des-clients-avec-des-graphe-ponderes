<?php require 'connect_db.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats - Gestion MLM</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
             background-color: #f8fafc;
             }
        .page-transition {
             animation: fadeIn 0.5s ease-out;
             }
        @keyframes fadeIn {
             from {
                 opacity: 0; transform: translateY(10px);
                 } to { opacity: 1; transform: translateY(0); } }
        
        /* Personnalisation des inputs pour un look "Premium" */
        input, select {
            transition: all 0.2s ease;
        }
        input:focus, select:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
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

<main class="max-w-5xl mx-auto px-4 py-10 page-transition">
    
    <div class=" mb-10">
        <h1 class="text-2xl font-bold text-gray-900">Gérer les Achats</h1>
        <p class="text-gray-500 text-sm mt-1 font-medium">Enregistrez les transactions pour calculer les futures commissions.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 mb-12">
        <div class="flex items-center gap-3 mb-6">
            <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800">Ajouter une transaction</h2>
        </div>

        <form action="traitement_achats.php" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Client</label>
                <select name="client_id" class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none bg-gray-50/50" required>
                    <option value="">Sélectionner un client</option>
                    <?php
                        $sql = "SELECT * FROM t_clients order by nom";
                        $requete = $connexion->query($sql);
                        while($clients= $requete->fetch()){
                            echo '<option value="'.$clients['id'].'">'.$clients['nom'].'</option>';
                        }
                    ?>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Montant ($)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                    <input type="number" name="montant" placeholder="0.00" min="1" step="0.01" 
                           class="w-full border border-gray-200 rounded-xl p-3 pl-8 text-sm outline-none" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Date</label>
                <input type="date" name="date_achat" value="<?= date('Y-m-d') ?>"
                       class="w-full border border-gray-200 rounded-xl p-3 text-sm outline-none" required>
            </div>

            <div class="md:col-span-3 flex justify-end pt-2">
                <button type="submit" class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold transition-all flex items-center gap-2 shadow-md hover:shadow-indigo-200">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Confirmer l'achat
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
            <h2 class="font-bold text-gray-800">Dernières transactions</h2>
            <span class="px-3 py-1 bg-white border border-gray-200 rounded-full text-[11px] font-bold text-gray-500 uppercase tracking-tighter">
                <?php echo $connexion->query("SELECT COUNT(*) FROM t_achats")->fetchColumn(); ?> Achats total
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-gray-400 text-[11px] uppercase tracking-widest border-b border-gray-50">
                        <th class="px-6 py-4 font-bold">Client</th>
                        <th class="px-6 py-4 font-bold">Date</th>
                        <th class="px-6 py-4 font-bold text-right">Montant</th>
                        <th class="px-6 py-4 font-bold text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php 
                    $sql = "SELECT t_achats.id, t_clients.nom, t_achats.montant, t_achats.date_achat 
                            FROM t_achats 
                            JOIN t_clients ON t_achats.client_id = t_clients.id 
                            ORDER BY t_achats.date_achat DESC";
                    $requete = $connexion->query($sql);
                    while($achat = $requete->fetch()):
                    ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-[10px] font-black italic">
                                    <?= strtoupper(substr($achat['nom'], 0, 1)) ?>
                                </div>
                                <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($achat['nom']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-medium text-gray-400">
                            <?= date('d/m/Y', strtotime($achat['date_achat'])) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-sm font-bold text-gray-900 tracking-tight">
                                <?= number_format($achat['montant'], 2, ',', ' ') ?> $
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                <button class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>