# Guide de contribution

Merci de contribuer au projet. Ce document fixe les règles à suivre pour que le code reste lisible, testé et facile à maintenir. L'installation et l'exécution des tests sont décrites dans le [README](README.md).


## 1. Principes

- Une contribution traite **un seul sujet**. Une correction de bug et une nouvelle fonctionnalité font l'objet de deux Pull Requests distinctes.
- Aucun code n'est fusionné sans **tests** et sans **revue**.
- On ne contourne jamais la sécurité : pas d'identifiants ni de secrets dans le dépôt, pas de désactivation de la protection CSRF.

## 2. Branches

| Branche | Rôle | Règles |
|---|---|---|
| `main` | Code stable, prêt à être déployé | Aucun commit direct. Alimentée uniquement par Pull Request validée |
| `develop` | Intégration des développements en cours | Aucun commit direct. Alimentée par Pull Request |
| `feature/…`, `fix/…`, etc. | Une branche par sujet, créée **à partir de `develop`** | Supprimée après la fusion |

### Nommage

Format : `<type>/<description-courte-en-minuscules-avec-tirets>`

| Préfixe | Usage | Exemple |
|---|---|---|
| `feature/` | Nouvelle fonctionnalité | `feature/blocage-invites` |
| `fix/` | Correction d'un bug | `fix/pagination-medias` |
| `hotfix/` | Correction urgente en production (créée depuis `main`) | `hotfix/csrf-connexion` |
| `refactor/` | Réorganisation du code sans changement de comportement | `refactor/media-uploader` |
| `test/` | Ajout ou amélioration de tests | `test/security-controller` |
| `docs/` | Documentation uniquement | `docs/readme-installation` |
| `chore/` | Maintenance, dépendances, configuration | `chore/maj-symfony-7-4` |
| `perf/` | Optimisation de performances | `perf/page-invites` |

## 3. Commits

Les messages suivent la convention **Conventional Commits** :

```
<type>(<portée facultative>): <description à l'impératif, en français, sans point final>

[corps facultatif : pourquoi ce changement]
```

Types autorisés : `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `chore`, `style`, `ci`.

Exemples :

```
feat(guest): bloquer un invité depuis le Back Office
fix(media): corriger le calcul du nombre de pages
perf(guests): remplacer le comptage par une jointure SQL
test(security): couvrir la connexion d'un invité bloqué
chore: mettre à jour les recettes Flex
```

Règles :

- Titre de **72 caractères maximum**.
- Un commit = une modification logique. Pas de commit « divers » ou « wip » sur une branche destinée à être relue.
- Ne commitez jamais `.env.local`, `.env.*.local`, `var/`, `vendor/` ni un fichier contenant un mot de passe, une clé d'API ou un `APP_SECRET`. Si un secret est publié par erreur, **changez-le immédiatement** : réécrire l'historique ne suffit pas.
- Vérifiez avec `git status` avant `git add`, et évitez `git add -A` sans relecture.

## 4. Procédure de contribution

1. **Créer ou choisir une tâche** (issue) décrivant le besoin.
2. **Se synchroniser**, puis créer sa branche depuis `develop` :
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/ma-fonctionnalite
   ```
3. **Développer** par petits commits, en suivant les conventions ci-dessus.
4. **Écrire ou adapter les tests** (voir partie 6).
5. **Vérifier en local** que tout passe :
   ```bash
   php bin/phpunit
   php bin/console lint:container
   php bin/console lint:twig templates
   php bin/console lint:yaml config
   php bin/console doctrine:schema:validate
   composer audit
   ```
6. **Mettre à jour la documentation** si le comportement ou l'installation changent (README, ce fichier).
7. **Pousser la branche** et ouvrir une **Pull Request vers `develop`**, en remplissant le modèle de la partie  7.
8. **Traiter les retours de revue** par de nouveaux commits sur la même branche.
9. Après la fusion, **supprimer la branche** et resynchroniser sa copie locale :
   ```bash
   git checkout develop
   git pull origin develop
   git branch -d feature/ma-fonctionnalite
   ```

> **Attention au mode de fusion.** Avec « Squash and merge », les commits de la branche disparaissent de l'historique et sont remplacés par un nouveau commit. Ne continuez donc pas à travailler sur une branche déjà fusionnée : créez-en une nouvelle depuis `develop`. Ce piège provoque l'affichage de commits « en avance » sur la Pull Request suivante.

## 5. Politique de validation

Une Pull Request est fusionnée **uniquement si toutes** les conditions suivantes sont remplies :

| Condition | Détail |
|---|---|
| **Intégration continue verte** | Tous les tests passent, les contrôles de qualité sont validés |
| **Au moins 1 approbation** | Un relecteur autre que l'auteur valide la contribution |
| **Aucun commentaire non résolu** | Chaque remarque est traitée ou discutée |
| **Couverture de code ≥ 70 %** | Mesurée sur l'ensemble du projet ; elle ne doit pas baisser |
| **Branche à jour** | Rebasée ou fusionnée avec `develop`, sans conflit |
| **Description complète** | Modèle de la partie 7 renseigné |

Mode de fusion :

- Branches de fonctionnalité vers `develop` : **Squash and merge**, avec un message de commit conforme à la partie  3.
- `develop` vers `main` : **Merge commit**, pour conserver les commits d'origine et éviter le décalage d'historique.

Le relecteur vérifie notamment :

- le respect du périmètre annoncé ;
- la présence et la pertinence des tests ;
- l'absence de secret, de code mort et de débogage oublié (`dump()`, `dd()`) ;
- les impacts sur la sécurité et les performances.

## 6. Bonnes pratiques

### Tests

- Toute fonctionnalité ajoute ses tests ; tout bug corrigé ajoute **un test qui échouait avant la correction**.
- **Tests unitaires** (`tests/Unit`) pour la logique isolée : entités, services, `UserChecker`.
- **Tests fonctionnels** (`tests/Functional`) pour les pages et les routes : cas nominal, formulaire invalide (code 422), identifiant inconnu (404), accès refusé (403) et visiteur non connecté (redirection vers `/login`).
- Les tests sont **indépendants** : aucun test ne dépend de l'ordre d'exécution ni des données créées par un autre. Un test qui crée une donnée la crée lui-même.
- Un test ne doit jamais supprimer ou modifier les vraies images de `public/uploads/`.
- Seuil à respecter : **couverture supérieure à 70 %**.

### Qualité et analyse statique

Avant chaque Pull Request, exécuter les contrôles de la procédure (partie 4, étape 5). En complément, le projet recommande :

```bash
vendor/bin/phpstan analyse src tests        # analyse statique
vendor/bin/php-cs-fixer fix --dry-run --diff  # style de code (PSR-12 / Symfony)
```

Ces deux outils s'installent avec `composer require --dev phpstan/phpstan friendsofphp/php-cs-fixer`.

### Code

- Respecter **PSR-12** et les conventions Symfony. Noms de classes en `PascalCase`, méthodes et variables en `camelCase`, routes en `snake_case`.
- Utiliser les **attributs PHP** (`#[Route]`, `#[ORM\…]`, `#[Assert\…]`), jamais les annotations.
- **Injecter les dépendances** (autowiring) plutôt que d'accéder au conteneur ou d'appeler des méthodes dépréciées.
- Pour récupérer une entité depuis l'URL, typer l'argument du contrôleur (`Album $album`) : Symfony renvoie une 404 si elle n'existe pas. 
- Les contrôleurs restent **fins** : la logique métier va dans les entités, les repositories ou des services.
- Typer les paramètres et les retours des méthodes.
- Commenter le **pourquoi**, pas le **quoi**.

### Sécurité

- **Toute action qui modifie des données** (création, modification, suppression, blocage) passe par une requête **POST** protégée par un **jeton CSRF**.
- Ne jamais faire confiance aux données de l'utilisateur : valider avec les contraintes (`#[Assert\…]`) et vérifier les droits (`#[IsGranted]`, `isGranted()`).
- Pour un fichier téléversé : contrôler le type et le poids, et **générer soi-même le nom** du fichier.
- Les mots de passe sont toujours hachés avec `UserPasswordHasherInterface`.
- Aucun secret dans le dépôt : utiliser `.env.local` ou les variables d'environnement.
- Lancer `composer audit` et traiter les vulnérabilités signalées.

### Base de données

- Toute modification d'une entité qui change le schéma passe par une **migration** (`make:migration`), **relue** avant d'être commitée. Ne jamais utiliser `doctrine:schema:update --force`.
- Vérifier la cohérence avec `php bin/console doctrine:schema:validate`.
- Une colonne ajoutée à une table déjà remplie doit avoir une valeur par défaut ou être nullable.

### Performances

- Avant de fusionner une page qui affiche des listes, ouvrir le **Profiler Symfony** (onglet *Doctrine*) et vérifier que le nombre de requêtes **ne dépend pas du nombre d'éléments affichés**.
- Éviter d'accéder à une relation dans une boucle Twig (`element.relation`) sans l'avoir chargée par jointure : c'est l'origine du problème « N+1 » corrigé sur la page Invités.
- Pour un simple comptage, préférer un `COUNT` SQL au chargement des entités.
- Ne pas utiliser `fetch: "EAGER"` par défaut sur les relations.

### Dépendances

- Ne pas ajouter de dépendance sans nécessité. Justifier tout ajout dans la Pull Request.
- Après une mise à jour, lancer `composer audit` et la suite de tests, puis commiter `composer.lock`.
- Les montées de version majeures de Symfony se font **par paliers de versions LTS**, en traitant les dépréciations à chaque étape.

## 7. Modèle de description de Pull Request

```markdown
## Contexte
<Pourquoi cette modification ? Lien vers l'issue.>

## Changements
- …
- …

## Comment tester
1. …
2. …

## Checklist
- [ ] Les tests ajoutés ou adaptés passent (`php bin/phpunit`)
- [ ] La couverture de code reste supérieure à 70 %
- [ ] `lint:container`, `lint:twig`, `lint:yaml` et `doctrine:schema:validate` passent
- [ ] `composer audit` ne signale aucune vulnérabilité
- [ ] Pas de secret, de `dump()` ni de code mort
- [ ] Une migration a été ajoutée et relue si le schéma change
- [ ] La documentation (README, CONTRIBUTING) est à jour
- [ ] Impact sur les performances vérifié (Profiler Symfony) si la page affiche une liste
```
