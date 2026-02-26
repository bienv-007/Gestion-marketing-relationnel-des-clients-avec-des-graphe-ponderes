<?php require 'connect_db.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f8fafc; }
        
        /* Animation d'apparition */
        .page-entry {
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Effet sur les lignes du tableau */
        .client-row {
            transition: all 0.2s ease;
        }
        .client-row:hover {
            background-color: #f1f5f9;
            transform: scale(1.002);
        }
    </style>
</head>
<body>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
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

<main class="max-w-6xl mx-auto px-4 py-10 page-entry">
    <div class="mb-10">
        <h1 class="text-2xl font-bold text-gray-900">Gérer les Clients</h1>
        <p class="text-gray-500 text-sm">Ajoutez, modifiez ou gérez les membres de votre réseau de parrainage.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-10">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i data-lucide="user-plus" class="w-5 h-5 text-indigo-600"></i>
            Ajouter un nouveau membre
        </h2>
        <form action="traitement_client.php" method="post" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                </div>
                <input type="text" name="nom" placeholder="Nom complet du client" 
                       class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm" 
                       required>
            </div>
            <button type="submit" class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Ajouter au réseau
            </button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="font-bold text-gray-800 tracking-tight">Liste des membres enregistrés</h2>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-bold uppercase">
                <?php echo $connexion->query("SELECT COUNT(*) FROM t_clients")->fetchColumn(); ?> membres
            </span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4 font-bold">Identifiant</th>
                        <th class="px-6 py-4 font-bold">Nom du Membre</th>
                        <th class="px-6 py-4 font-bold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    $sql = "SELECT * FROM t_clients ORDER BY id DESC";
                    $requete = $connexion->query($sql);
                    while($client = $requete->fetch()): 
                    ?>
                    <tr class="client-row group">
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono text-gray-400">#<?= str_pad($client['id'], 3, '0', STR_PAD_LEFT) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                    <?= strtoupper(substr($client['nom'], 0, 1)) ?>
                                </div>
                                <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($client['nom']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-3">
                                <button class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Modifier">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
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