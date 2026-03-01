<?php require 'connect_db.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relations - MLM</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { background-color: #f8fafc; }
        
        .card-anim {
            animation: fadeIn 0.5s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Style pour les selects */
        select {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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

<main x-data="{ 
        showModal: false, 
        relationToDelete: null,
        rowToDelete: null,
        confirmDelete() {
            fetch('supprimer_relation.php?id=' + this.relationToDelete)
                .then(response => {
                    if(response.ok) {
                        this.rowToDelete.isVisible = false;
                        this.showModal = false;
                    }
                });
        }
    }" 
    class="max-w-6xl mx-auto px-4 py-10 card-anim">
    <div class="mb-10">
        <h1 class="text-2xl font-bold text-gray-900">Gérer les Relations de Parrainage</h1>
        <p class="text-gray-500 text-sm mt-1">Établissez les liens hiérarchiques entre les membres de votre réseau.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-12">
        <div class="flex items-center gap-2 mb-6">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                <i data-lucide="git-branch-plus" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800">Ajouter une nouvelle liaison</h2>
        </div>

        <form action="traitement_relations.php" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Parrain</label>
                <select name="client_parrain" class="w-full p-3 border border-gray-200 rounded-xl text-sm outline-none bg-gray-50/50" required>
                    <option value="">Sélectionnez un parrain</option>
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
                <label class="text-xs font-bold text-gray-400 uppercase ml-1">Filleul</label>
                <select name="client_filleul" class="w-full p-3 border border-gray-200 rounded-xl text-sm outline-none bg-gray-50/50" required>
                    <option value="">Sélectionnez un filleul</option>
                    <?php
                        $requete = $connexion->query($sql); // Réutiliser la même requête triée
                        while($clients= $requete->fetch()){
                            echo '<option value="'.$clients['id'].'">'.$clients['nom'].'</option>';
                        }
                    ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Créer la relation
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-50 bg-gray-50/30 flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Arborescence du réseau</h3>
            <span class="text-xs font-bold bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full uppercase">
                <?php echo $connexion->query("SELECT COUNT(*) FROM t_relations")->fetchColumn(); ?> Liens
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 text-[11px] uppercase tracking-widest border-b border-gray-100">
                        <th class="px-8 py-4 font-bold">Parrain</th>
                        <th class="px-8 py-4 font-bold text-center">Direction</th>
                        <th class="px-8 py-4 font-bold">Filleul</th>
                        <th class="px-8 py-4 font-bold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
    <?php
    $sql = "SELECT r.id, c1.nom AS parrain, c2.nom AS filleul 
            FROM t_relations r 
            JOIN t_clients c1 ON r.parrain_id = c1.id 
            JOIN t_clients c2 ON r.filleuil_id = c2.id";
    $requete = $connexion->query($sql);
    while($rel = $requete->fetch()):
    ?>
    <tr x-data="{ isVisible: true }" 
        x-show="isVisible" 
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="hover:bg-indigo-50/40 transition-colors group">
        
        <td class="px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($rel['parrain']) ?></span>
            </div>
        </td>

        <td class="px-8 py-5 text-center">
            <div class="inline-flex items-center justify-center text-indigo-300 group-hover:text-indigo-600 transition-colors">
                <i data-lucide="arrow-right-circle" class="w-6 h-6"></i>
            </div>
        </td>

        <td class="px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white shadow-sm">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($rel['filleul']) ?></span>
            </div>
        </td>

        <td class="px-8 py-5 text-right">
            <button 
                @click="if(confirm('Supprimer cette relation ?')) {
                    fetch('supprimer_relation.php?id=<?= $rel['id'] ?>')
                    .then(response => {
                        if(response.ok) isVisible = false;
                    })
                }"
                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"
            >
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
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