# 🍔 Food Battle

Application web permettant de découvrir des spécialités culinaires (burgers, tacos, pizzas), de voter pour ses favoris, de participer à des duels, de tirer un repas au sort avec **La Roue du Destin** et de laisser un avis. Un espace membre permet de gérer son profil et un espace d'administration protégé par rôle (`admin`) permet de gérer les plats, les établissements et la modération des avis.

Projet de reprise réalisé pour **PLAYGROUND** : le code a été commencé par un stagiaire, puis audité, corrigé, sécurisé et complété par notre équipe (**Sara et Loren**) dans le cadre de la formation **Concepteur·rice développeur·se d'applications (CDA)**.

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Technologies](#technologies)
3. [Installation et lancement en local](#installation-et-lancement-en-local)
4. [Commandes utiles](#commandes-utiles)
5. [Structure du projet](#structure-du-projet)
6. [Base de données et migrations](#base-de-données-et-migrations)
7. [Création du compte administrateur initial](#création-du-compte-administrateur-initial)
8. [Déploiement en production (Render)](#déploiement-en-production-render)
9. [Sécurité](#sécurité)
10. [Limites connues](#limites-connues)
11. [Organisation du travail](#organisation-du-travail)
12. [Dépannage](#dépannage)

---

## Fonctionnalités

### 🌍 Espace public
- **Accueil & Podium Top 3** : mise en avant visuelle des 3 meilleurs plats du moment, recherche par nom de plat ou de restaurant, et filtrage rapide par catégorie (`👑 Tous`, `🍔 Burgers`, `🌮 Tacos`, `🍕 Pizzas`).
- **Fiche détail d'un plat (`plat.php`)** : photographie du plat, établissement, adresse et ville, prix, moyenne des notes et liste des avis publiés.
- **⚔️ Duel de plats (`duel.php`)** : affrontement aléatoire entre deux spécialités (filtrable par catégorie) permettant d'attribuer `+1 vote` à son plat préféré.
- **🎰 La Roue du Destin — « Je ne sais pas quoi manger ! » (`roue.php`)** *(Challenge Surprise Client)* :
  - Sélection aléatoire d'un plat réel en base de données avec animation de roue et défilement à suspense.
  - Filtrage combiné par **ville** (`📍 Ma ville`) et par **catégorie** avant le tirage.
  - **Carte Google Maps intégrée** avec marqueur jaune personnalisé localisant le restaurant du plat gagnant.
  - Effets sonores activables/désactivables (Web Audio API), bouton **« 🔄 Retenter ma chance ! »** et **historique des 4 derniers tirages**.
- **👑 Classement (`classement.php`)** : top 10 des meilleurs plats avec tri dynamique par **nombre de votes** ou par **note moyenne**.
- **Navigation responsive** : interface adaptée aux écrans mobiles (`<= 768px`) avec menu déroulant et cartes compactes.

### 👤 Espace utilisateur connecté (`role = 'user'`)
- **Inscription (`inscription.php`) et Connexion (`login.php`)** sécurisées avec hachage des mots de passe (`bcrypt`).
- **Vote pour un plat** : un vote unique par utilisateur et par plat (avec indicateur visuel `♥`).
- **Publication d'un avis** : note de 1 à 5 étoiles et commentaire (soumis à la modération avant publication).
- **Gestion du profil utilisateur** : consultation et mise à jour des informations du compte utilisateur.

### 🛠️ Espace d'administration (`role = 'admin'`)
- **🍔 Gestion des plats (`dashboard.php`, `modifier.php`, `supprimer.php`)** :
  - Consultation, ajout d'un plat avec téléversement d'image (stockage Cloud via **Cloudinary**), association à un établissement, modification et suppression.
- **🏪 Gestion des établissements (`etablissements.php`, `modifier-etablissement.php`, `supprimer-etablissement.php`)** :
  - Consultation avec compteur de plats associés, ajout, modification (nom, ville, adresse) et suppression avec cascade (`ON DELETE CASCADE`).
- **💬 Modération des avis (`moderation.php`, `moderation-action.php`)** :
  - Validation (`published`) ou refus (`rejected`) des avis en attente (`pending`).

---

## Technologies

| Élément | Choix |
|---|---|
| Langage | PHP 8.5 (sans framework), serveur Apache |
| Base de données | PostgreSQL 16, accès via PDO |
| Stockage d'images | Cloudinary API (avec image de secours locale / Pexels) |
| Cartographie | Google Maps Embed |
| Environnement local | Docker et Docker Compose |
| Hébergement production | Render (Web Service Docker + Render PostgreSQL) |
| Front-end | HTML5, CSS3 modulaire (responsive mobile-first) et JavaScript natif |

Nous avons choisi de **conserver l'architecture PHP / PostgreSQL native** : le code existant a été assaini, modularisé et sécurisé sans alourdir le projet avec un framework externe.

---

## Installation et lancement en local

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
| `APP_PORT` | Port du site sur votre machine (`8080` par défaut) |
| `DB_NAME`, `DB_USER` | Nom et utilisateur de la base PostgreSQL |
| `DB_PASSWORD` | Mot de passe de l'utilisateur PostgreSQL |
| `ADMIN_NICKNAME` | Pseudo du compte administrateur initial |
| `ADMIN_EMAIL` | Adresse e-mail du compte administrateur initial |
| `ADMIN_PASSWORD_HASH` | Empreinte (`hash` bcrypt) du mot de passe administrateur |
| `CLOUDINARY_CLOUD_NAME` | Nom du cloud Cloudinary pour l'upload des photos de plats |
| `CLOUDINARY_API_KEY` | Clé API Cloudinary |
| `CLOUDINARY_API_SECRET` | Secret API Cloudinary |

Le fichier `.env` est dans le `.gitignore` : il n'est jamais envoyé sur GitHub. Sans lui, `docker compose` s'arrête avec le message `Copie .env.example vers .env`.

### 3. Générer le hash du mot de passe administrateur

```bash
docker compose run --rm --no-deps app php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
```

Copier le résultat dans `.env` **entre apostrophes simples** :

```env
ADMIN_PASSWORD_HASH='$2y$10$...'
```

> ⚠️ Avec des guillemets doubles dans `.env`, Docker Compose interprète les `$` comme des variables et **tronque le hash** : la connexion échoue.

### 4. Lancer l'application

```bash
docker compose up --build
```

Puis ouvrir **http://localhost:8080** (ou le port choisi dans `APP_PORT`).

Au premier lancement, la base est créée et remplie avec 6 établissements et 12 plats de démonstration grâce à `database/init.sql`.

---

## Commandes utiles

| Action | Commande |
|---|---|
| Arrêter l'application | `docker compose down` |
| Voir les logs (erreurs PHP, Apache) | `docker compose logs app` |
| Reconstruire après un changement du `Dockerfile` | `docker compose build --no-cache` puis `docker compose up -d` |
| Réinitialiser la base (⚠️ efface toutes les données) | `docker compose down -v` puis `docker compose up --build` |
| Ouvrir la base en ligne de commande | `docker compose exec db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'` |
| Appliquer un fichier de migration SQL | `docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"' < database/migrations/NOM_DU_FICHIER.sql` |
| Vérifier la version de PHP | `docker compose exec app php -v` |

---

## Structure du projet

```text
.
├── app/
│   ├── index.php                     Accueil, recherche, filtres et Podium Top 3
│   ├── plat.php                      Fiche détail d'un plat, vote et dépôt d'avis
│   ├── duel.php                      Duel aléatoire entre 2 plats (+1 vote)
│   ├── roue.php                      « La Roue du Destin » (tirage aléatoire, filtre ville/catégorie, carte)
│   ├── classement.php                Top 10 des plats (par votes ou par note moyenne)
│   ├── action.php                    Traitement POST des votes et des avis
│   ├── login.php                     Connexion utilisateur et administrateur
│   ├── inscription.php               Création d'un compte utilisateur
│   ├── logout.php                    Déconnexion et destruction de session
│   ├── profil.php                    Gestion du profil utilisateur connecté
│   ├── dashboard.php                 Admin : liste et ajout de plats (avec upload photo)
│   ├── modifier.php                  Admin : modification d'un plat
│   ├── supprimer.php                 Admin : suppression d'un plat
│   ├── etablissements.php            Admin : liste et ajout d'établissements
│   ├── modifier-etablissement.php    Admin : modification d'un établissement
│   ├── supprimer-etablissement.php   Admin : suppression d'un établissement (cascade)
│   ├── moderation.php                Admin : liste et modération des avis
│   ├── moderation-action.php         Admin : approbation ou refus d'un avis
│   ├── includes/                     En-tête, pied de page, fonctions, connexion BDD, Cloudinary et contrôle d'accès
│   └── assets/
│       ├── style.css                 Feuille de style principale
│       ├── app.js                    Confirmation des suppressions et interactions UI
│       ├── images/                   Logo, mascotte et placeholder.svg
│       └── css/                      Modules CSS (variables, header, home, cards, detail, duel, roue, ranking, admin, components)
├── database/
│   ├── init.sql                      Schéma initial et données de démonstration
│   ├── create-admin.php              Script CLI de création du compte administrateur initial
│   └── migrations/                   Scripts SQL d'évolution du schéma (000 à 008+)
├── compose.yaml
├── Dockerfile
├── .env.example
└── LANCEMENT.txt
```

---

## Base de données et migrations

### Tables principales

| Table | Contenu |
|---|---|
| `establishments` | Établissements (nom, ville, adresse) |
| `dishes` | Plats (établissement, nom, catégorie, description, prix, `image_url`) |
| `ratings` | Avis (plat, utilisateur, pseudo, note de 1 à 5, commentaire, statut) |
| `votes` | Votes (plat, utilisateur connecté et/ou clé visiteur hachée) |
| `users` | Comptes utilisateurs (pseudo, e-mail unique, hash du mot de passe, rôle `user`/`admin`, date de création) |
| `schema_migrations` | Registre des migrations SQL appliquées |

### Mise à jour d'une base existante (Migrations)

Après avoir récupéré une branche contenant de nouvelles migrations dans `database/migrations/`, exécutez-les dans l'ordre numérique sans supprimer votre volume :

```bash
for f in database/migrations/*.sql; do
  docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"' < "$f"
done
```

Principales migrations du projet :
- `000-create-migration-history.sql` : création de la table `schema_migrations`.
- `001-add-missing-constraints.sql` : ajout des contraintes d'intégrité et index sur les clés étrangères.
- `002-create-users-table.sql` : création de la table `users` (`user` / `admin`).
- `003` à `007` : liaison des votes et des avis aux comptes utilisateurs (`user_id`), ajout de la colonne `image_url` sur `dishes`, contraintes d'unicité.
- `008-establishments-cascade-delete.sql` : ajout de `ON DELETE CASCADE` sur `dishes.establishment_id` pour permettre la suppression propre d'un établissement et de ses plats associés.

Règles d'intégrité appliquées **par PostgreSQL** :
- Clés étrangères avec `ON DELETE CASCADE` (supprimer un établissement supprime ses plats ; supprimer un plat supprime ses avis et ses votes).
- `dishes.category` limitée à `burger`, `tacos`, `pizza`.
- `dishes.price` supérieur ou égal à `0`.
- `ratings.score` compris entre `1` et `5`.
- `ratings.status` limité à `pending`, `published`, `rejected`.
- Adresses e-mail uniques dans `users` et rôles limités à `user` ou `admin`.

---

## Création du compte administrateur initial

Une fois les tables et migrations en place et les variables `ADMIN_NICKNAME`, `ADMIN_EMAIL` et `ADMIN_PASSWORD_HASH` renseignées dans `.env`, exécutez :

```bash
docker compose exec app php /var/www/database/create-admin.php
```

Ce script crée le compte administrateur dans la table `users` avec le rôle `admin`. La connexion sur `/login.php` s'effectue ensuite avec l'adresse e-mail et le mot de passe administrateur.

---

## Déploiement en production (Render)

L'application est déployée sur **Render** à l'aide de deux services :
1. **Render PostgreSQL (`Free`)** : base de données PostgreSQL 16 hébergée dans la région `Frankfurt (EU Central)`.
2. **Render Web Service (`Docker`)** : conteneur PHP 8.5 / Apache construit à partir du `Dockerfile` de la branche `main`.

### Variables d'environnement à configurer sur Render (Web Service)
- `DB_HOST`, `DB_PORT` (`5432`), `DB_NAME`, `DB_USER`, `DB_PASSWORD` *(fournies par la base PostgreSQL Render)*
- `ADMIN_NICKNAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD_HASH` *(sans apostrophes autour du hash dans l'interface Render)*
- `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`

### Initialisation de la base Render depuis le terminal local
En utilisant l'`External Database URL` fournie par Render :

```bash
# 1. Schéma initial et données de démonstration
docker compose exec -T db psql "EXTERNAL_DATABASE_URL" < database/init.sql

# 2. Application de toutes les migrations
for f in database/migrations/*.sql; do
  docker compose exec -T db psql "EXTERNAL_DATABASE_URL" < "$f"
done
```

---

## Sécurité

- **Requêtes préparées (PDO)** sur l'ensemble des requêtes SQL : protection contre les injections SQL.
- **Protection CSRF** : jeton de session vérifié sur tous les formulaires `POST` (votes, avis, connexion, inscription, profil, administration).
- **Échappement systématique des sorties (`h()`)** : protection contre les failles XSS.
- **Authentification par base de données (`users`)** : mots de passe hachés avec `password_hash()` (`PASSWORD_DEFAULT`) et vérifiés avec `password_verify()`.
- **Contrôle d'accès par rôle (`includes/auth.php`)** : accès au tableau de bord réservé exclusivement aux comptes ayant `$_SESSION['user_role'] === 'admin'`.
- **Sessions sécurisées** : régénération de l'identifiant de session à la connexion (`session_regenerate_id(true)`) et destruction complète à la déconnexion.
- **Validation des fichiers uploadés** : vérification du type MIME réel (`finfo`), de la taille maximale (5 Mo) et hébergement distant sur Cloudinary.
- **Listes blanches** sur tous les filtres URL (`categorie`, `ville`, `tri`).
- **Aucun secret dans le code** : toutes les clés et mots de passe sont passés par variables d'environnement (`.env`).

---

## Limites connues

- **Hébergement gratuit Render** : sur l'offre gratuite de Render, le conteneur web se met en veille après 15 minutes d'inactivité (le premier chargement peut alors prendre 30 à 50 secondes).
- **Vote en mode Duel pour les visiteurs non connectés** : dans l'arène Duel, l'empreinte de session (`SHA-256`) est utilisée si l'utilisateur n'est pas connecté ; sur les fiches plats, la connexion à un compte utilisateur est requise pour voter et déposer un avis.

---

## Organisation du travail

- **Un ticket GitHub (Issue) par fonctionnalité ou correctif** (étiquettes `bug`, `security`, `feature`, `enhancement`, `ui`, `docs`).
- **Une branche par ticket** (`feat/...`, `fix/...`), fusionnée vers `dev` puis `main` via **Pull Request**.
- **Revue de code croisée** entre **Sara** et **Loren** avant chaque fusion, avec description détaillée des changements et procédure de test.
- Messages de commit normalisés (`Feature: ...`, `Fix: ...`, `Refactor: ...`, `Docs: ...`).

---

## Dépannage

| Symptôme | Cause probable et solution |
|---|---|
| `Copie .env.example vers .env` au lancement | Le fichier `.env` est absent ou incomplet : le créer avec `cp .env.example .env` |
| `address already in use` sur le port `5432` | Un service PostgreSQL local tourne déjà sur la machine : l'arrêter avec `sudo service postgresql stop` (Linux/WSL) avant `docker compose up -d` |
| `could not translate host name "db"` | Le conteneur `db` est arrêté ou sur Render `DB_HOST` vaut encore `db` au lieu du hostname Render (`dpg-...`) |
| `password authentication failed` sur Render | Vérifier que `DB_USER`, `DB_PASSWORD` et `DB_NAME` dans Render Environment correspondent exactement aux valeurs de la section *Connections* de Render PostgreSQL |
| `relation "dishes" does not exist` sur Render | La base Render est vide : importer `database/init.sql` et `database/migrations/*.sql` via l'External Database URL |
| Les modifications CSS ne s'affichent pas | Vider le cache du navigateur avec un rechargement forcé (`Ctrl + Shift + R` ou `Cmd + Shift + R`) |
| Le port `8080` est déjà utilisé | Modifier `APP_PORT` dans `.env` (ex. `APP_PORT=8081`) |
