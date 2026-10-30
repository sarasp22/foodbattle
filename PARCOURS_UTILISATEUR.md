# 🧭 Parcours Utilisateur — Food Battle (PLAYGROUND)

Ce document décrit l'ensemble des **parcours utilisateurs (User Journeys)** de l'application **Food Battle**, selon les trois profils d'utilisateurs : **Visiteur non connecté**, **Membre inscrit (`user`)** et **Administrateur (`admin`)**.

---

## 👥 1. Les Profils d'Utilisateurs (Rôles)

| Profil | Rôle en base | Objectif principal |
|---|---|---|
| **Visiteur (Public)** | *Non connecté* | Découvrir les spécialités culinaires, rechercher un plat, consulter le podium et le classement, participer aux duels et utiliser **La Roue du Destin**. |
| **Membre connecté** | `user` | Voter pour ses plats favoris, déposer des avis notés (1 à 5 étoiles) et gérer les informations de son profil personnel. |
| **Administrateur** | `admin` | Gérer le catalogue des plats (CRUD + upload photo), gérer les établissements (CRUD) et modérer les avis déposés par les membres. |

---

## 🗺️ 2. Vue d'ensemble de la navigation

```text
                             ┌──────────────────────────┐
                             │    Accueil (index.php)   │
                             │  • Recherche & Filtres   │
                             │  • Podium Top 3          │
                             │  • Grille des plats      │
                             └────────────┬─────────────┘
                                          │
       ┌──────────────────┬───────────────┼───────────────┬──────────────────┐
       ▼                  ▼               ▼               ▼                  ▼
┌──────────────┐  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ Fiche détail │  │ ⚔️ Duel      │ │ 🎰 Roue du   │ │ 👑 Classement│ │ Authentif.   │
│  (plat.php)  │  │  (duel.php)  │ │    Destin    │ │(classement)  │ │ (login.php / │
│              │  │              │ │  (roue.php)  │ │              │ │ inscription) │
│ • Infos plat │  │ • 2 plats    │ │ • Filtre     │ │ • Top 10     │ └──────┬───────┘
│ • Vote (👍)  │  │   aléatoires │ │   ville/cat. │ │ • Tri votes  │        │
│ • Avis (★)   │  │ • +1 vote    │ │ • Animation  │ │   ou note    │        ▼
└──────────────┘  └──────────────┘ │ • Carte Maps │ └──────────────┘ ┌──────────────┐
                                   │ • Historique │                  │ Connecté :   │
                                   └──────────────┘                  │ • Profil     │
                                                                     │ • Dashboard  │
                                                                     │   (si admin) │
                                                                     └──────────────┘
```

---

## 🚶 3. Détail des Parcours Utilisateurs

### Parcours 1 — Découverte et recherche d'un plat (Visiteur & Membre)
1. **Arrivée sur la page d'accueil (`index.php`)** :
   - L'utilisateur découvre la bannière principale, le bouton d'accès rapide à **La Roue du Destin**, ainsi que le **Podium des 3 meilleurs plats** (classés par nombre de votes, avec la 1ère place surélevée au centre).
   - En dessous s'affiche la grille complète des spécialités disponibles.
2. **Recherche et filtrage** :
   - L'utilisateur peut saisir un mot-clé dans la barre de recherche (nom d'un plat ou d'un restaurant) et/ou cliquer sur une pastille de catégorie (`👑 Tous`, `🍔 Burgers`, `🌮 Tacos`, `🍕 Pizzas`).
   - Il peut également cliquer sur **« Plats »** (ou sur la loupe `🔍`) dans l'en-tête pour accéder directement à la vue catalogue (`index.php?view=plats#tous-les-plats`).
3. **Consultation de la fiche d'un plat (`plat.php?id=...`)** :
   - En cliquant sur la carte d'un plat, l'utilisateur accède à sa fiche détaillée : photographie grand format, prix en euros, nom du restaurant, adresse et ville, note moyenne sur 5 étoiles, compteur de votes et liste des avis validés par la modération.

---

### Parcours 2 — Le Classement des 10 meilleurs plats (`classement.php`)
1. Depuis le menu principal ou le lien **« Voir le classement → »**, l'utilisateur ouvre la page **Classement**.
2. Par défaut, les 10 meilleurs plats sont classés **par nombre de votes** décroissant.
3. En un clic sur l'onglet **« Par note moyenne »** (`classement.php?tri=note`), le classement se réorganise selon la moyenne des notes publiées (sur 5 étoiles).
4. Chaque ligne affiche le rang (`1` à `10`), la miniature réelle du plat, son nom (cliquable vers la fiche détail), le restaurant, le nombre de votes et la note moyenne.

---

### Parcours 3 — L'Arène de Duel (`duel.php`)
1. L'utilisateur clique sur **« ⚔️ Duel »** dans la barre de navigation.
2. Il peut filtrer l'affrontement par catégorie (`👑 Tous`, `🍔 Burgers`, `🌮 Tacos`, `🍕 Pizzas`).
3. Deux plats tirés aléatoirement (parmi ceux pour lesquels il n'a pas encore voté en duel) s'affrontent côte à côte avec un badge **VS**.
4. L'utilisateur clique sur **« ⚡ Je choisis ce plat ! »** sous son plat préféré :
   - Le plat choisi reçoit immédiatement **`+1 vote`**.
   - Un message de confirmation s'affiche (`🔥 +1 vote enregistré pour ... ! Place au duel suivant :`) et un nouveau duel est proposé automatiquement.
   - Si l'utilisateur veut changer d'affiche sans voter, il clique sur **« 🔄 Tirer un autre duel au sort »**.

---

### Parcours 4 — « Je ne sais pas quoi manger ! » : La Roue du Destin (`roue.php`)
*Fonctionnalité développée dans le cadre du Challenge Surprise Client (PLAYGROUND).*

1. **Point d'entrée** :
   - L'utilisateur clique sur le bouton central **« 🎰 Je ne sais pas quoi manger ! — Lancer la Roue → »** sur la page d'accueil, ou sur **« 🎰 Roue du Destin »** dans le menu de navigation.
2. **Personnalisation avant tirage** :
   - **📍 Choix de la ville** : l'utilisateur sélectionne sa ville dans le menu déroulant (alimenté dynamiquement par les villes des restaurants en base) ou laisse *« Toutes les villes »*.
   - **🍔 Choix de la catégorie** : il filtre éventuellement par *Burgers*, *Tacos* ou *Pizzas*.
   - **🔊 Effets sonores** : il peut activer ou couper les effets sonores à tout moment.
3. **Tirage animé et révélation** :
   - Au chargement ou au clic sur **« 🔄 Retenter ma chance ! »**, la roue tourne pendant 2,5 secondes tandis qu'un bandeau défile les plats candidats avec suspense.
   - À l'arrêt de la roue, la carte du **plat gagnant** est révélée avec sa photo, sa catégorie, son prix, son restaurant, son adresse, sa note moyenne et un bouton **« 🍴 Voir la fiche du plat → »**.
4. **Localisation sur la carte & Historique** :
   - Juste en dessous du plat gagnant, une **carte Google Maps interactive** affiche l'emplacement exact du restaurant avec un **marqueur jaune animé** portant le nom de l'établissement.
   - Sous la roue, la section **« 📜 Historique de tes derniers tirages »** conserve les **4 derniers plats tirés** durant la session pour permettre à l'utilisateur d'y revenir en un clic.
   - Si aucun plat ne correspond aux filtres choisis, un message clair invite l'utilisateur à réinitialiser les filtres.

---

### Parcours 5 — Inscription, Connexion et Gestion du Profil (`inscription.php`, `login.php`, `profil.php`)
1. **Création de compte (`inscription.php`)** :
   - Le visiteur clique sur **« Inscription »** dans l'en-tête.
   - Il renseigne un **pseudo** (max 60 caractères), une **adresse e-mail** valide et unique, ainsi qu'un **mot de passe sécurisé** (minimum 12 caractères, confirmé une seconde fois).
   - Après validation, son compte est créé avec le rôle `user`.
2. **Connexion (`login.php`)** :
   - L'utilisateur saisit son e-mail et son mot de passe.
   - L'identifiant de session est régénéré pour la sécurité :
     - S'il a le rôle `user`, il est redirigé vers l'accueil avec la mention **« Bonjour [Pseudo] »** dans l'en-tête.
     - S'il a le rôle `admin`, il est redirigé vers le tableau de bord administrateur (`dashboard.php`).
3. **Gestion du profil (`profil.php`)** :
   - Une fois connecté, le membre accède à son espace profil pour consulter et modifier ses informations personnelles (pseudo, e-mail, mot de passe) et retrouver son activité.

---

### Parcours 6 — Voter et déposer un avis sur un plat (`plat.php` → `action.php`)
1. Connecté à son compte, l'utilisateur ouvre la fiche d'un plat (`plat.php?id=...`).
2. **Voter pour le plat** :
   - Il clique sur **« 👍 Voter pour ce plat »**.
   - Son vote est enregistré en base (1 seul vote autorisé par utilisateur et par plat).
   - Un badge **« ✔️ Vous avez voté pour ce plat »** ainsi qu'un cœur rouge **`♥`** sur la photo du plat confirment son vote.
3. **Publier un avis** :
   - En bas de la fiche, l'utilisateur remplit le formulaire **« Ajouter un avis »** (pseudo pré-rempli, note de `1/5` à `5/5` et commentaire jusqu'à 1000 caractères) puis clique sur **« Publier mon avis »**.
   - L'avis est enregistré avec le statut **`pending` (en attente de modération)** : il ne sera visible publiquement et comptabilisé dans la moyenne qu'après validation par un administrateur.

---

### Parcours 7 — Administration et Modération (`role = 'admin'`)
L'administrateur se connecte sur `login.php` et accède au **Tableau de bord** (également accessible à tout moment via l'icône `👤` dans l'en-tête). La barre latérale lui permet de naviguer entre 3 espaces :

1. **🍔 Gestion des plats (`dashboard.php`, `modifier.php`, `supprimer.php`)** :
   - **Ajouter un plat** : clic sur **« + Ajouter un plat »**, saisie du nom, sélection de l'établissement dans la liste déroulante, choix de la catégorie, du prix, upload d'une photographie (JPG, PNG ou WebP jusqu'à 5 Mo hébergée sur **Cloudinary**) et description.
   - **Modifier un plat** : clic sur l'icône `✏️`, modification du nom, de l'établissement ou du prix, puis retour rapide via le bouton **« ← Retour aux plats »**.
   - **Supprimer un plat** : clic sur l'icône `🗑️` avec fenêtre de confirmation ; la suppression efface également en cascade les votes et avis rattachés à ce plat.
2. **🏪 Gestion des établissements (`etablissements.php`, `modifier-etablissement.php`, `supprimer-etablissement.php`)** :
   - **Consulter** : tableau listant tous les restaurants, leur ville, leur adresse et le nombre de plats associés.
   - **Ajouter** : clic sur **« + Ajouter un établissement »** (nom, ville, adresse).
   - **Modifier** : clic sur `✏️` pour mettre à jour le nom, la ville ou l'adresse du restaurant.
   - **Supprimer** : clic sur `🗑️` avec message d'avertissement indiquant le nombre de plats qui seront supprimés en cascade.
3. **💬 Modération des avis (`moderation.php`, `moderation-action.php`)** :
   - Visualisation de tous les avis déposés par les utilisateurs (plat concerné, pseudo, note en étoiles, commentaire et statut).
   - Pour chaque avis au statut `pending`, l'administrateur clique sur **« Approuver »** (l'avis passe à `published` et apparaît sur la fiche du plat) ou sur **« Refuser »** (l'avis passe à `rejected`).

---

## 📱 4. Expérience Mobile & Responsive (`<= 768px`)

Une attention particulière a été portée à l'utilisation sur smartphone :
- **Menu déroulant mobile (`☰ Menu`)** : sur les écrans mobiles, les 5 rubriques principales (`Accueil`, `Plats`, `⚔️ Duel`, `🎰 Roue du Destin`, `Classement`) sont regroupées dans un menu déroulant fluide pour éviter tout retour à la ligne dans l'en-tête.
- **Cartes compactes style Application** : les grilles de plats s'affichent sur 2 colonnes compactes et la page **La Roue du Destin** adapte la taille de la roue, de la fiche gagnante et de l'historique (grille 2×2 des 4 derniers tirages) aux petits écrans.

---

## 🔐 5. Matrice récapitulative des droits par profil

| Fonctionnalité | Visiteur non connecté | Membre connecté (`user`) | Administrateur (`admin`) |
|---|:---:|:---:|:---:|
| Parcourir les plats, rechercher et filtrer | ✅ | ✅ | ✅ |
| Consulter le Podium Top 3 et le Classement Top 10 | ✅ | ✅ | ✅ |
| Utiliser **La Roue du Destin** (ville, catégorie, carte) | ✅ | ✅ | ✅ |
| Participer aux **Duels** de plats | ✅ | ✅ | ✅ |
| Voter sur la fiche détail d'un plat (`plat.php`) | ❌ *(connexion requise)* | ✅ | ✅ |
| Déposer un avis noté (soumis à modération) | ❌ *(connexion requise)* | ✅ | ✅ |
| Gérer son profil utilisateur (`profil.php`) | ❌ | ✅ | ✅ |
| Ajouter / Modifier / Supprimer des **plats** | ❌ | ❌ | ✅ |
| Ajouter / Modifier / Supprimer des **établissements** | ❌ | ❌ | ✅ |
| Approuver ou refuser les **avis** (`moderation.php`) | ❌ | ❌ | ✅ |
