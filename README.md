# 🍔 Food Battle

Application web permettant de découvrir des spécialités culinaires (burgers, tacos, pizzas), de voter pour ses favoris et de laisser un avis. Un espace d'administration protégé par mot de passe permet de gérer les plats.

Projet de reprise réalisé pour **PLAYGROUND** : le code a été commencé par un stagiaire, puis audité, corrigé et complété par notre équipe (Sara et Loren) dans le cadre de la formation Concepteur·rice développeur·se d'applications.

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Technologies](#technologies)
3. [Installation et lancement](#installation-et-lancement)
4. [Commandes utiles](#commandes-utiles)
5. [Structure du projet](#structure-du-projet)
6. [Base de données et migrations](#base-de-données-et-migrations)
7. [Création du compte administrateur initial](#création-du-compte-administrateur-initial)
8. [Sécurité](#sécurité)
9. [Limites connues](#limites-connues)
10. [Organisation du travail](#organisation-du-travail)
11. [Dépannage](#dépannage)

## Fonctionnalités

**Espace public**

- Liste des plats avec recherche par nom et filtre par catégorie (burger, tacos, pizza)
- Page de détail d'un plat : établissement, adresse, prix, avis publiés
- Vote pour un plat (un vote par plat et par navigateur)
- Dépôt d'un avis avec pseudo (60 caractères maximum), note de 1 à 5 et commentaire (1000 caractères maximum)
- Classement des 10 meilleurs plats, avec tri par nombre de votes ou par note moyenne

**Espace d'administration** (connexion par mot de passe sur `/login.php`, lien « Déconnexion » dans le menu une fois connecté)

- Liste des plats : consultation, ajout, modification (nom et prix), suppression
- Modération des avis : approbation ou refus.

## Technologies

| Élément | Choix |
|---|---|
| Langage | PHP 8.5 (sans framework), serveur Apache |
| Base de données | PostgreSQL 16, accès via PDO |
| Environnement | Docker et Docker Compose |
| Front | HTML, CSS et un peu de JavaScript natif |

Nous avons choisi de **ne pas changer de langage ni de framework** : le code existant était exploitable et le coût d'une réécriture complète n'était pas justifié.

## Installation et lancement

### Prérequis

- [Docker](https://docs.docker.com/get-docker/) avec Docker Compose
- Git

### 1. Récupérer le projet

```bash
git clone https://github.com/SIMPLON-DIST-CDA-P9-200473/foodbattle_SS_LV.git
cd foodbattle_SS_LV
```

### 2. Créer le fichier `.env`

```bash
cp .env.example .env
```

Ouvrir `.env` et renseigner les valeurs :

| Variable | Rôle |
|---|---|
| `APP_PORT` | Port du site sur votre machine (8080 par défaut) |
| `DB_NAME`, `DB_USER` | Nom et utilisateur de la base PostgreSQL |
| `DB_PASSWORD` | Mot de passe de la base : Mot de passe de l'utilisateur PostgreSQL |
| `ADMIN_NICKNAME` | Pseudo du compte administrateur initial |
| `ADMIN_EMAIL` | Adresse e-mail du compte administrateur initial |
| `ADMIN_PASSWORD_HASH` | Empreinte (hash) du mot de passe administrateur |

Le fichier `.env` est dans le `.gitignore` : il n'est jamais envoyé sur GitHub. Sans lui, `docker compose` s'arrête avec le message `Copie .env.example vers .env`.

### 3. Générer le hash du mot de passe administrateur

```bash
docker compose run --rm --no-deps app php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
```

Copier le résultat dans `.env` **entre apostrophes simples** :

```
ADMIN_PASSWORD_HASH='$2y$10$...'
```

> ⚠️ Avec des guillemets doubles, Docker Compose interprète les `$` comme des variables et **tronque le hash** : la connexion échoue.

### 4. Lancer l'application

```bash
docker compose up --build
```

Puis ouvrir **http://localhost:8080** (ou le port choisi dans `APP_PORT`).

Au premier lancement, la base est créée et remplie avec 12 plats fictifs, grâce à `database/init.sql`.

## Commandes utiles

| Action | Commande |
|---|---|
| Arrêter l'application | `docker compose down` |
| Voir les logs (erreurs PHP, Apache) | `docker compose logs app` |
| Reconstruire après un changement du `Dockerfile` | `docker compose build --no-cache` puis `docker compose up -d` |
| Réinitialiser la base (⚠️ efface toutes les données) | `docker compose down -v` puis `docker compose up --build` |
| Ouvrir la base en ligne de commande | `docker compose exec db psql -U <DB_USER> -d <DB_NAME>` |
| Vérifier la version de PHP | `docker compose exec app php -v` |

Le script `database/init.sql` n'est exécuté que **lorsque la base est créée**. Toute modification du schéma nécessite donc `docker compose down -v`.

## Structure du projet

```
.
├── app/
│   ├── index.php
│   ├── plat.php
│   ├── classement.php
│   ├── action.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── modifier.php
│   ├── supprimer.php
│   ├── moderation.php
│   ├── includes/             En-tête, pied de page, fonctions, connexion BDD et contrôle d'accès
│   └── assets/               CSS, JavaScript et images
├── database/
│   ├── init.sql              Schéma et données de démonstration pour une base vide
│   ├── create-admin.php       Script CLI de création du compte administrateur initial
│   └── migrations/
│       ├── 000-create-migration-history.sql
│       ├── 001-add-missing-constraints.sql
│       └── 002-create-users-table.sql
├── compose.yaml
├── Dockerfile
├── .env.example
└── LANCEMENT.txt
```

## Base de données et migrations

Quatre tables :

| Table | Contenu |
|---|---|
| `establishments` | Établissements (nom, ville, adresse) |
| `dishes` | Plats (établissement, nom, catégorie, description, prix) |
| `ratings` | Avis (plat, pseudo, note, commentaire, statut) |
| `votes` | Votes (plat, clé du visiteur) |
| `users` | Comptes utilisateurs (pseudo, e-mail, hash du mot de passe, rôle et date de création) |
| `schema_migrations` | Registre des migrations appliquées |

### Initialisation d'une base vide

Lors du premier lancement, `database/init.sql` initialise une base vide.
Il ne met pas à jour une base déjà créée.

### Mise à jour d'une base locale existante

Les migrations ne sont pas lancées automatiquement. Après avoir récupéré
une branche qui ajoute ou modifie les migrations, chaque membre doit les
appliquer à sa base PostgreSQL locale.

**Ne réinitialisez pas votre base et ne supprimez pas le volume
`foodbattle_data` pour appliquer ces migrations.**

Dans votre outil SQL, sélectionnez la connexion PostgreSQL à la base
`foodbattle`, puis exécutez les fichiers suivants dans l'ordre :

1. `database/migrations/000-create-migration-history.sql`
2. `database/migrations/001-add-missing-constraints.sql`
3. `database/migrations/002-create-users-table.sql`

Après exécution, vérifiez que `schema_migrations` contient les
identifiants `001-add-missing-constraints` et `002-create-users-table`,
puis vérifiez que la table `users` et ses contraintes existent.

Le fichier `000-create-migration-history.sql` crée le registre des
migrations ; il ne s'inscrit pas lui-même dans ce registre. Les
migrations suivantes enregistrent leur identifiant après leur exécution.
Même si les scripts prennent en compte certaines contraintes ou tables
déjà présentes, exécutez-les dans l'ordre prévu et vérifiez leur
résultat.

Règles appliquées **par la base elle-même** :

- Clés étrangères avec `ON DELETE CASCADE` : supprimer un plat supprime aussi ses avis et ses votes
- `dishes.category` limitée à `burger`, `tacos`, `pizza`
- `dishes.price` supérieur ou égal à 0
- `ratings.score` entre 1 et 5
- `ratings.status` limité à `pending`, `published`, `rejected`
- `votes` : contrainte `UNIQUE (dish_id, visitor_key)`, un seul vote par visiteur et par plat
- Les adresses e-mail de `users` sont uniques.
- Le rôle de `users` est limité à `user` ou `admin`.
- Index sur les clés étrangères utilisées dans les recherches

## Création du compte administrateur initial

Le script `database/create-admin.php` permet de créer le compte
administrateur initial dans la table `users`. Il doit être lancé
manuellement en ligne de commande, après la création de la table et la
configuration des variables d'environnement dans `.env`.

Vérifiez que les variables suivantes sont renseignées dans `.env` :

- `ADMIN_NICKNAME`
- `ADMIN_EMAIL`
- `ADMIN_PASSWORD_HASH`

Le hash est déjà configuré si vous avez correctement renseigné cette
variable dans votre `.env`. Ne renseignez pas le mot de passe en clair à
la place du hash.

Depuis la racine du projet, lancez la commande :

```bash
docker compose exec app php /var/www/database/create-admin.php
```

Le script vérifie les variables, le format de l'adresse e-mail et la
longueur du pseudo. Il refuse de créer un doublon : si un administrateur
existe déjà à cette adresse, il signale que le compte existe ; si
l'adresse appartient à un utilisateur non administrateur, il signale une
erreur.

Vérifiez ensuite dans la table `users` que le compte a été créé avec le
rôle `admin`.

**Important :** cette procédure crée le compte dans la table `users`. La
connexion à l'espace d'administration existant reste basée sur
`ADMIN_PASSWORD_HASH` tant que l'authentification n'a pas été adaptée
pour rechercher les comptes dans `users`.

## Sécurité

- **Requêtes préparées** (PDO) : protection contre les injections SQL
- **Protection CSRF** : jeton vérifié sur tous les formulaires qui envoient des données
- **Échappement des sorties** avec la fonction `h()` : protection contre les failles XSS (y compris dans les commentaires)
- **Espace d'administration protégé** par mot de passe : le tableau de bord, la modification, la suppression et la modération vérifient la connexion avant tout traitement (`includes/auth.php`)
- **Session administrateur** régénérée à la connexion (`session_regenerate_id`) et entièrement détruite à la déconnexion
- **Suppression en POST** uniquement, avec jeton CSRF
- **Validation côté serveur** des avis (longueurs contrôlées avec `mb_strlen`, cohérentes avec les colonnes de la base)
- **Aucun secret dans le code** : identifiants de la base et hash administrateur dans `.env`
- **Identifiant de session haché** (SHA-256) avant d'être enregistré avec un vote
- **Listes blanches** pour les valeurs venant de l'URL (catégories, critères de tri)

## Limites connues

- **Vote unique par navigateur** : le vote est lié à la session. Un visiteur qui vide ses cookies ou utilise la navigation privée peut voter à nouveau. Sans comptes utilisateurs, il n'existe pas de moyen fiable de l'éviter.
- **Historique Git** : l'ancien mot de passe de la base de développement reste visible dans les anciens commits. Il s'agit d'un prototype local avec des données fictives ; dans un projet réel, il serait considéré comme compromis et remplacé.
- **Images** : les photos viennent d'un service externe (Pexels) et nécessitent une connexion Internet. Une image de secours s'affiche en cas d'échec. <!-- À METTRE À JOUR après P14 -->
- **Duels** : le sujet mentionne des duels entre plats, mais ils sont **hors du périmètre du MVP** validé lors de la revue de projet.
- **Authentification des comptes utilisateurs** : la table `users` et les rôles sont en place, mais la connexion à l'espace d'administration utilise encore ADMIN_PASSWORD_HASH` et n'authentifie pas les comptes de cette table.<!-- À METTRE À JOUR après P38 -->

## Organisation du travail

- Un **ticket GitHub par problème** (étiquettes `bug`, `security`, `refactor`, `ui`, `docs`, jalons « MVP » et « Après MVP »)
- **Une branche par ticket**, jamais de commit direct sur `main`
- Une **Pull Request relue** par l'autre membre de l'équipe avant le merge, avec une description et des preuves (captures, logs)
- Messages de commit courts, du type `Fix: ...`, `Feature: ...`, `Refactor: ...`

Après une Pull Request qui modifie le schéma de la base, consultez les migrations fournies et appliquez-les à votre base locale. **Ne réinitialisez pas votre base avec docker compose down -v` pour appliquer une migration**, sauf si vous souhaitez volontairement supprimer toutes les données locales.

## Dépannage

| Symptôme | Cause probable et solution |
|---|---|
| `Copie .env.example vers .env` au lancement | Le fichier `.env` est absent ou incomplet : le créer à partir de `.env.example` |
| Page blanche | Erreur PHP : lire `docker compose logs app` |
| Le compte administrateur n'est pas créé | Vérifiez que la table `users` existe et que `ADMIN_NICKNAME`, `ADMIN_EMAIL` et `ADMIN_PASSWORD_HASH` sont définis dans `.env` |
| Connexion administrateur toujours refusée | Hash tronqué : vérifier les **apostrophes simples** dans `.env` (voir étape 3) |
| La table `users` ou une contrainte manque | Vérifiez que les migrations ont été exécutées dans l'ordre et que leurs identifiants figurent dans               `schema_migrations` |
| La base refuse la connexion après un changement de mot de passe | PostgreSQL ne lit `DB_PASSWORD` qu'à la création : `docker compose down -v` |
| Une nouvelle règle de la base n'est pas appliquée | `init.sql` ne s'exécute qu'à la création : `docker compose down -v` |
| Le port 8080 est déjà utilisé | Changer `APP_PORT` dans `.env` |
| Avertissement `variable is not set` de Compose | Un `$` dans `.env` est interprété : utiliser des apostrophes simples |
| L'outil SQL ne peut pas se connecter à PostgreSQL depuis l'ordinateur | Vérifiez que le port est publié dans `compose.yaml` avec `127.0.0.1:5432:5432`, puis recréez   les conteneurs sans supprimer le volume |
