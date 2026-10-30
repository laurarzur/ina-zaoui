# Ina Zaoui – Site de portfolio photographique

Application web Symfony présentant le portfolio de la photographe Ina Zaoui et les photographies de ses invités.

- **Front Office** : accueil, portfolio (filtrable par album), liste des invités, page d'un invité, page « Qui suis-je ? ».
- **Back Office** : gestion des albums, des invités (création, blocage, suppression) et des médias.

Le projet est le résultat de la reprise d'une application Symfony 5.4, migrée vers **Symfony 7.4 LTS**, corrigée, testée et optimisée.

## 1. Prérequis

- PHP >= 8.4
- Composer 
- Symfony CLI 

Le projet est configuré pour une utilisation avec MySQL/MariaDB.  
Cependant, vous pouvez tout à fait utiliser un autre SGBD.

## 2. Installation

```bash
# 1. Récupérer le projet
git clone https://github.com/laurarzur/ina-zaoui.git
cd ina-zaoui

# 2. Installer les dépendances
composer install
```

### Configuration de l'environnement

Ne modifiez jamais `.env` (fichier versionné, valeurs par défaut). Créez un fichier **`.env.local`**, ignoré par Git, qui contient vos valeurs :

```dotenv
APP_ENV=dev
APP_SECRET=<chaîne aléatoire de 32 caractères hexadécimaux>
DATABASE_URL="mysql://utilisateur:motdepasse@127.0.0.1:3306/ina_zaoui?serverVersion=8.0.36&charset=utf8mb4"
```

- Générer un secret : `php -r "echo bin2hex(random_bytes(16));"`
- `serverVersion` doit être la version **complète** du serveur (`SELECT VERSION();`).  
Pour MariaDB, préfixer par `mariadb-` (ex. `mariadb-10.11.6`).

### Base de données et données de démonstration

**Si vous utilisez MySQL/MariaDB :** 
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate 
php bin/console doctrine:fixtures:load 
```

**Si vous n'utilisez pas MySQL/MariaDB :**  
Supprimez les fichiers du dossier `/migrations` 
```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate 
php bin/console doctrine:fixtures:load 
```

Les fixtures créent 1 administrateur, 99 invités, 5 albums et 5 050 médias.

> `doctrine:fixtures:load` **vide** la base avant de la remplir.

### Images

Les fixtures référencent des fichiers `public/uploads/0001.jpg` à `public/uploads/5000.jpg`.  
Ces images doivent être présentes dans `public/uploads/` pour s'afficher.

### Lancer le serveur

```bash
symfony server:start
```

Le site est alors accessible sur l'adresse affichée (par défaut `https://127.0.0.1:8000` ou `http://127.0.0.1:8000`).

## 3. Comptes de démonstration

Tous les comptes ont le mot de passe **`password`**.

| Fixtures | Compte | Rôle |
|---|---|---|
| Développement (`dev`) | `ina@zaoui.com` | administratrice |
| Développement (`dev`) | `invite+0@example.com`, `invite+1@example.com`, … | invités |
| Tests (`test`) | `ina@zaoui.com` | administratrice |
| Tests (`test`) | `invite1@example.com`, `invite2@example.com` | invités actifs |
| Tests (`test`) | `bloque@example.com` | invité **bloqué** (connexion refusée) |

Répartition des données de développement : l'administratrice possède 50 médias répartis dans 5 albums (10 par album) ; chaque invité possède 50 médias, sans album. Les titres vont de « Titre 0 » à « Titre 49 ».

## 4. Lancer les tests

Les tests utilisent **PHPUnit** et une **base de données dédiée** (`<nom_de_la_base>_test`), distincte de celle de développement.

### Préparer la base de test (une fois)

Créez `.env.test.local` (ignoré par Git) avec l'URL de la base de test, puis :

```bash
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --group=test --no-interaction
```

Le jeu de données de test est volontairement réduit (1 administrateur, 3 invités dont 1 bloqué, 2 albums, 19 médias) pour que la suite soit rapide. Il est défini dans `src/DataFixtures/TestFixtures.php`.

### Exécuter les tests

```bash
php bin/phpunit                                  # toute la suite
php bin/phpunit tests/Unit                       # tests unitaires seulement
php bin/phpunit tests/Functional                 # tests fonctionnels seulement
php bin/phpunit --filter testGuestAddsMedia      # un test précis
```

Certains tests fonctionnels **écrivent** en base (ajout, blocage, suppression). Si des résultats paraissent incohérents d'un lancement à l'autre, rechargez les fixtures de test avec la commande ci-dessus.

### Rapport de couverture de code

Nécessite Xdebug (mode `coverage`) ou PCOV.

```bash
# Linux / macOS
XDEBUG_MODE=coverage php bin/phpunit --coverage-text
XDEBUG_MODE=coverage php bin/phpunit --coverage-html var/coverage
```

```powershell
# Windows (PowerShell)
$env:XDEBUG_MODE = "coverage"
php bin/phpunit --coverage-text
php bin/phpunit --coverage-html var/coverage
```

Le rapport HTML s'ouvre via `var/coverage/index.html`. **L'objectif du projet est une couverture supérieure à 70 %.**

### Organisation des tests

```
tests/
├── Unit/                       Tests unitaires (sans base de données ni HTTP)
│   ├── Entity/                 User, Media, Album
│   └── Security/               UserChecker
├── Functional/                 Tests fonctionnels (requêtes HTTP + base de test)
│   ├── FunctionalTestCase.php  Classe de base : client, user(), loginAdmin()
│   ├── Controller/             HomeController, AlbumController, GuestController,
│   │                           MediaController, SecurityController
│   └── fixtures/               Fichiers utilisés par les tests d'upload (photo.jpg, faux.jpg)
```

## 5. Documentation du code

### 5.1 Vue d'ensemble

Application Symfony classique en architecture **MVC** :

- **Contrôleurs** (`src/Controller`) : reçoivent la requête, appellent les repositories, rendent un template.
- **Entités et repositories** (`src/Entity`, `src/Repository`) : modèle de données (Doctrine ORM) et requêtes.
- **Formulaires** (`src/Form`) : formulaires Symfony avec validation.
- **Sécurité** (`src/Security`, `config/packages/security.yaml`) : authentification et contrôle d'accès.
- **Vues** (`templates/`) : templates Twig, style **Bootstrap 5.3** chargé depuis un CDN.

### 5.2 Arborescence

```
src/
├── Controller/
│   ├── HomeController.php          Front Office (public)
│   └── Admin/
│       ├── AlbumController.php     CRUD des albums
│       ├── GuestController.php     Gestion des invités
│       ├── MediaController.php     Gestion des médias
│       └── SecurityController.php  Connexion / déconnexion
├── DataFixtures/
│   ├── AppFixtures.php             Données de développement (groupe dev)
│   └── TestFixtures.php            Données de test (groupe test)
├── Entity/                         User, Media, Album
├── EventListener/
│   └── MediaFileRemover.php        Supprime le fichier image quand un média est supprimé
├── Form/                           AlbumType, GuestType, MediaType
├── Repository/                     UserRepository, MediaRepository, AlbumRepository
└── Security/
    └── UserChecker.php             Refuse la connexion des invités bloqués
templates/
├── front/                          Pages publiques
├── admin/                          Pages du Back Office et page de connexion
└── base.html.twig, front.html.twig, admin.html.twig
```

### 5.3 Modèle de données

```mermaid
erDiagram
    USER |o--o{ MEDIA : "possède"
    ALBUM |o--o{ MEDIA : "contient"
    USER {
        int id
        string email
        json roles
        string password
        string name
        text description
        bool isBlocked
    }
    MEDIA {
        int id
        string path
        string title
    }
    ALBUM {
        int id
        string name
    }
```

- **`User`** : implémente `UserInterface` et `PasswordAuthenticatedUserInterface`. L'identifiant de connexion est l'**e-mail**. Les rôles sont stockés en JSON ; `ROLE_USER` est toujours ajouté par `getRoles()`. L'administrateur porte `ROLE_ADMIN`. Le champ `isBlocked` désactive l'accès d'un invité. Les mots de passe sont **hachés** avec le service `UserPasswordHasherInterface`, jamais stockés en clair.
- **`Media`** : un média appartient à un utilisateur et, facultativement, à un album (réservé aux médias de l'administratrice). `path` contient le chemin relatif du fichier (`uploads/xxxx.jpg`). La propriété `file` n'est pas persistée : elle transporte le fichier envoyé par le formulaire.
- **`Album`** : regroupe des médias de l'administratrice.
- **Suppression en cascade** : `User::$medias` est déclaré avec `orphanRemoval: true`. Supprimer un invité supprime donc ses médias en base.

### 5.4 Routes

**Front Office (public)**

| Route | URL | Description |
|---|---|---|
| `home` | `/` | Accueil |
| `guests` | `/guests` | Liste des invités **non bloqués**, avec leur nombre de médias |
| `guest` | `/guest/{id}` | Page d'un invité (redirige vers `/guests` s'il est bloqué) |
| `portfolio` | `/portfolio/{id}` | Médias de l'administratrice ; avec `{id}`, médias d'un album |
| `about` | `/about` | Page « Qui suis-je ? » |
| `login` / `logout` | `/login`, `/logout` | Authentification |

**Back Office (authentification requise)**

| Route | URL | Accès |
|---|---|---|
| `admin_media_index` / `admin_media_add` / `admin_media_delete` | `/admin/media/…` | administrateur **et invités** (un invité ne voit et ne gère que ses médias) |
| `admin_album_index` / `_add` / `_update` / `_delete` | `/admin/album/…` | administrateur |
| `admin_guest_index` / `_add` / `_block` / `_unblock` / `_delete` | `/admin/guest/…` | administrateur |

### 5.5 Sécurité

- **Authentification** : `form_login` sur `/login`, avec un *user provider* de type `entity` (classe `User`, propriété `email`). Il remplace l'ancien provider en mémoire, qui ne permettait qu'un seul compte écrit en dur dans `security.yaml`.
- **Protection CSRF** : activée sur le formulaire de connexion et sur les formulaires Symfony.
- **Blocage des invités** : `App\Security\UserChecker` est branché sur le firewall. Il lève une exception `CustomUserMessageAccountStatusException` pour un compte bloqué, avant même la vérification du mot de passe.
- **Contrôle d'accès** : `access_control` dans `security.yaml` réserve `/admin` aux utilisateurs connectés ; les pages d'albums et d'invités exigent `ROLE_ADMIN`.

### 5.6 Gestion des médias

1. **Ajout** (`MediaController::add`) : le formulaire `MediaType` valide le fichier (présence et type image, voir `Media::$file`). Un nom de fichier aléatoire est généré côté serveur, le fichier est déplacé dans `uploads/`, et le chemin relatif est enregistré en base. Un invité ajoute des médias à son propre compte ; l'administrateur peut choisir le propriétaire et l'album.
2. **Liste** : l'administrateur voit tous les médias, un invité seulement les siens. Pagination de **25 médias par page**, le numéro de page étant lu dans `?page=`.
3. **Suppression** : l'écouteur `MediaFileRemover` (événement Doctrine `postRemove`) supprime aussi le fichier du disque. Il fonctionne pour une suppression directe comme pour une suppression en cascade (suppression d'un invité).

### 5.7 Choix d'implémentation notables

| Choix | Raison |
|---|---|
| Résolution automatique des entités dans les contrôleurs (`Album $album` au lieu de `int $id` + `find()`) | Code plus court, et une **404 automatique** si l'identifiant n'existe pas |
| `Form` passé directement à `render()` (sans `createView()`) | Symfony répond alors en **422** si le formulaire est invalide |
| Rôles stockés en JSON, filtrés par `LIKE '%"ROLE_ADMIN"%'` dans les repositories | Évite une colonne booléenne dupliquant l'information ; fonctionne avec MySQL/MariaDB |
| Nombre de médias par invité calculé en SQL (`COUNT` + `GROUP BY`) | Supprime le problème « N+1 » de la page Invités (voir partie 7) |
| Mapping Doctrine par **attributs PHP** | Les annotations sont abandonnées ; ORM 3 ne les supporte plus |
| Suppression du fichier dans un écouteur Doctrine | Couvre tous les cas de suppression, pas seulement le contrôleur |
| Pages d'erreur personnalisées dans `templates/bundles/TwigBundle/Exception/` | Aucun code PHP nécessaire ; affichées uniquement en production |

## 6. Historique de la migration 5.4 → 7.4

Pour le prochain développeur, voici les changements structurants réalisés lors de la reprise :

- **Migration Symfony 5.4 → 6.4 → 7.4 LTS**, par paliers, en traitant les dépréciations à chaque étape (`getDoctrine()` remplacé par l'injection de repositories et de l'`EntityManager`, `reset_on_message` pour Messenger, version complète de MySQL dans `DATABASE_URL`, etc.).
- **Mise à jour des recettes Flex** (`composer recipes`) en conservant les personnalisations du projet (thème de formulaires Bootstrap 5, titre et assets de `base.html.twig`).
- **Authentification** : passage d'un utilisateur unique en mémoire à des utilisateurs stockés en base.
- **Correction des anomalies et nouvelles fonctionnalités** : blocage effectif des invités, suppression des invités avec leurs médias, validation des fichiers téléversés, protection CSRF, déconnexion ajoutée.
- **Doctrine ORM 3 / DBAL 3** et PHP 8.2 minimum.

## 7. Performances

La page « Invités » exécutait **101 requêtes SQL** pour 99 invités (problème « N+1 » : `guest.medias|length` chargeait les médias de chaque invité). La correction calcule le nombre de médias dans la requête de liste, avec `UserRepository::findActiveGuestsWithMediaCount()` : **1 seule requête**, et un temps de réponse divisé par plus de trois (environ 1 890 ms → 550 ms en environnement de développement).

Le détail de la méthode, des mesures et de l'analyse figure dans le **rapport de performance** du projet.

Pour détecter ce type de problème : ouvrir le **Profiler Symfony**, onglet *Doctrine*. Un grand nombre de requêtes presque identiques est le signe d'un N+1.

## 8. Points d'attention et pistes d'amélioration

- La page « Invités » n'est pas paginée : à prévoir si le nombre d'invités devient important.
- Images : ajouter `loading="lazy"` et des **miniatures** sur le portfolio pour alléger le chargement.
- Les photos sont affichées en pleine résolution ; un cache HTTP sur les pages publiques réduirait encore la charge.

## 9. Contribuer

Les règles de contribution (branches, commits, revue de code, qualité) sont décrites dans [CONTRIBUTING.md](CONTRIBUTING.md).
