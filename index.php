<?php
require 'connect_db.php';

// 1. Récupération des clients (Nœuds) avec avatars générés par initiales
$req_nodes = $connexion->query("SELECT id, nom AS label FROM t_clients");
$nodes = [];
foreach($req_nodes->fetchAll(PDO::FETCH_ASSOC) as $node) {
    $nodes[] = [
        'id' => $node['id'],
        'label' => $node['label'],
        'shape' => 'circularImage',
        'image' => 'https://ui-avatars.com/api/?name=' . urlencode($node['label']) . '&background=4f46e5&color=fff&size=128',
        'font' => ['face' => 'Inter, sans-serif', 'vadjust' => 10]
    ];
}

// 2. Récupération des relations (Liens)
$req_edges = $connexion->query("SELECT parrain_id AS 'from', filleuil_id AS 'to' FROM t_relations");
$edges = $req_edges->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - MLM</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f9fafb; }
        
        /* Animation fluide pour les cartes */
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        #monGraphe {
            width: 100%;
            height: 550px;
            background: #ffffff;
            border-radius: 12px;
        }
    </style>
</head>
<body>

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
                $current_page = basename($_SERVER['PHP_SELF']);
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

<main class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-10 text-center md:text-left">
        <h2 class="text-2xl font-bold text-gray-800">Vue d'ensemble du réseau</h2>
        <p class="text-gray-500 text-sm">Analysez la croissance de vos équipes en temps réel.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="stat-card bg-white p-6 rounded-xl border border-gray-200 flex items-center gap-5">
            <div class="bg-blue-50 p-4 rounded-lg text-blue-600">
                <i data-lucide="users" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Clients</p>
                <p class="text-2xl font-bold text-gray-900"><?= $connexion->query("SELECT COUNT(*) FROM t_clients")->fetchColumn(); ?></p>
            </div>
        </div>

        <div class="stat-card bg-white p-6 rounded-xl border border-gray-200 flex items-center gap-5">
            <div class="bg-purple-50 p-4 rounded-lg text-purple-600">
                <i data-lucide="git-branch" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Relations</p>
                <p class="text-2xl font-bold text-gray-900"><?= $connexion->query("SELECT COUNT(*) FROM t_relations")->fetchColumn(); ?></p>
            </div>
        </div>

        <div class="stat-card bg-white p-6 rounded-xl border border-gray-200 flex items-center gap-5">
            <div class="bg-emerald-50 p-4 rounded-lg text-emerald-600">
                <i data-lucide="shopping-bag" class="w-8 h-8"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Achats</p>
                <p class="text-2xl font-bold text-gray-900"><?= $connexion->query("SELECT COUNT(*) FROM t_achats")->fetchColumn(); ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-2 mb-4 px-2">
            <i data-lucide="share-2" class="w-5 h-5 text-indigo-600"></i>
            <span class="font-semibold text-gray-700">Visualisation dynamique du réseau</span>
        </div>
        <div id="monGraphe" class="border border-gray-50"></div>
    </div>
</main>

<script>
    lucide.createIcons();

    // Données provenant de PHP
    const nodes = new vis.DataSet(<?php echo json_encode($nodes); ?>);
    const edges = new vis.DataSet(<?php echo json_encode($edges); ?>);

    const container = document.getElementById('monGraphe');
    const data = { nodes, edges };

    const options = {
        nodes: {
            borderWidth: 2,
            size: 35,
            color: { border: '#4f46e5', background: '#ffffff' }
        },
        edges: {
            arrows: { to: { enabled: true, scaleFactor: 0.5 } },
            color: { color: '#cbd5e1', highlight: '#4f46e5' },
            smooth: { type: 'continuous' }
        },
        physics: {
            enabled: true,
            stabilization: { iterations: 200 },
            barnesHut: { gravitationalConstant: -3000, springLength: 150 }
        }
    };

    const network = new vis.Network(container, data, options);
</script>
</body>
</html>