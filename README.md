# Gestion MLM

Gestion MLM est une application web de gestion de marketing relationnel construite en PHP et MySQL. Elle permet de gérer des clients, de créer des relations de parrainage, d'enregistrer des achats et de calculer les commissions générées par un réseau de filleuls directs et indirects.

Démo : https://gestion-mlm.42web.io/

## Fonctionnalités

- Tableau de bord avec indicateurs clés : nombre de clients, relations et achats.
- Visualisation graphique du réseau de parrainage avec Vis Network.
- Gestion des clients : ajout, modification, suppression et détection des doublons.
- Gestion des relations : création et suppression de liens parrain/filleul.
- Contrôles métier sur les relations : impossibilité de se parrainer soi-même, limitation à un parrain par filleul et prévention des boucles dans le réseau.
- Gestion des achats : ajout, modification et suppression des transactions.
- Calcul des commissions par client.
- Classement des membres selon les commissions générées.
- Interface responsive basée sur Tailwind CSS, Alpine.js et Lucide Icons.

## Règles de commission

Le système calcule les commissions sur deux niveaux :

- Filleuls directs : 5 % du montant total de leurs achats.
- Filleuls indirects : 1 % du montant total des achats des filleuls de ses filleuls.

Exemple :

- Si un filleul direct effectue 100 $ d'achats, son parrain gagne 5 $.
- Si un filleul indirect effectue 100 $ d'achats, le parrain de niveau supérieur gagne 1 $.

## Technologies utilisées

- PHP avec PDO
- MySQL
- HTML
- Tailwind CSS via CDN
- Alpine.js via CDN
- Lucide Icons via CDN
- Vis Network pour la visualisation du graphe
- UI Avatars pour les avatars générés automatiquement

## Structure du projet

```text
gestion_mlm/
├── index.php                 # Tableau de bord et graphe du réseau
├── clients.php               # Interface de gestion des clients
├── traitement_client.php     # Traitements CRUD des clients
├── relations.php             # Interface de gestion des relations
├── traitement_relations.php  # Traitements des liens de parrainage
├── achats.php                # Interface de gestion des achats
├── traitement_achats.php     # Traitements CRUD des achats
├── commission.php            # Calculateur et classement des commissions
├── connect_db.php            # Connexion PDO à MySQL
├── db_mlm.sql                # Script SQL de création et données d'exemple
└── README.md
```

## Base de données

La base de données utilisée est `db_mlm`. Elle contient trois tables principales :

- `t_clients` : liste des membres du réseau.
- `t_relations` : liens entre parrains et filleuls.
- `t_achats` : achats réalisés par les clients.

Le fichier `db_mlm.sql` contient la structure complète de la base ainsi que des données d'exemple.

## Installation locale

### 1. Prérequis

- PHP 8 ou supérieur
- MySQL ou MariaDB
- Serveur local comme XAMPP, WAMP, MAMP ou Laragon
- Navigateur web moderne

### 2. Cloner ou copier le projet

Placez le dossier du projet dans le répertoire web de votre serveur local.

Avec XAMPP sous Linux, par exemple :

```bash
/opt/lampp/htdocs/gestion_mlm
```

### 3. Créer la base de données

Dans phpMyAdmin ou dans un terminal MySQL, créez une base de données nommée :

```sql
CREATE DATABASE db_mlm;
```

Importez ensuite le fichier :

```text
db_mlm.sql
```

### 4. Configurer la connexion

La connexion est définie dans `connect_db.php` :

```php
$connexion = new PDO("mysql:host=localhost;dbname=db_mlm", "root", "");
```

Adaptez les identifiants si votre environnement MySQL utilise un autre utilisateur, mot de passe, hôte ou nom de base.

### 5. Lancer l'application

Démarrez Apache et MySQL, puis ouvrez :

```text
http://localhost/gestion_mlm/
```

## Pages principales

- `index.php` : affiche la vue d'ensemble du réseau, les statistiques et le graphe des relations.
- `clients.php` : permet de gérer les membres.
- `relations.php` : permet de relier un filleul à un parrain.
- `achats.php` : permet d'enregistrer les transactions des clients.
- `commission.php` : permet de calculer les commissions et d'afficher le classement des revenus.

## Notes techniques

- L'application utilise une architecture PHP procédurale simple.
- Les requêtes sensibles sont majoritairement exécutées avec des requêtes préparées PDO.
- Les dépendances front-end sont chargées via CDN : une connexion Internet est donc nécessaire pour obtenir le rendu complet de l'interface.
- Le projet ne contient pas encore de système d'authentification, de rôles utilisateurs ou de protection CSRF.
- La suppression d'un client ne supprime pas automatiquement ses achats ou ses relations dans le code applicatif.

## Améliorations possibles

- Ajouter une authentification administrateur.
- Centraliser le layout commun de navigation pour éviter la duplication entre les pages.
- Ajouter des contraintes de clés étrangères avec InnoDB.
- Gérer automatiquement les suppressions liées aux clients.
- Ajouter des messages de succès et d'erreur dans les pages principales.
- Ajouter des tests fonctionnels sur les règles de commission et de relation.
- Externaliser les paramètres de connexion dans un fichier de configuration non versionné.

## Auteur

Projet développé dans le cadre d'une application de gestion MLM en PHP et MySQL.
