# laravel-media-library

Package Laravel réutilisable fournissant une médiathèque complète : upload de fichiers, organisation en dossiers (arborescence illimitée), et attachement polymorphique many-to-many entre médias et n'importe quel modèle de l'application (avatar utilisateur, thumbnail de capsule, galerie d'article, etc.).

## Pourquoi ce package

- **Un seul fichier peut être attaché à plusieurs modèles** grâce à la table pivot `media_attachments` (many-to-many polymorphique), contrairement à une relation classique où un média n'appartient qu'à un seul propriétaire.
- **Collections nommées** : un même modèle peut avoir plusieurs types de médias distincts (`avatar`, `cover`, `gallery`...) via le champ `collection_name`.
- **Dossiers hiérarchiques** avec protection anti-cycle (impossible de déplacer un dossier dans l'un de ses propres sous-dossiers).
- **Config centralisée** : disque de stockage, mimes acceptés, taille max, guard d'authentification — tout est ajustable par projet sans toucher au code du package.

## Prérequis

- PHP ^8.2
- Laravel ^11.0 ou ^12.0
- Un système d'authentification API compatible avec `auth:sanctum` (ou tout autre guard configurable)

## Installation

### 1. Déclarer le repository dans `composer.json`

Le package est hébergé sur un dépôt Git privé. Ajoute-le dans `composer.json` **avant** de faire le `require` :

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:skal-doe/laravel-media-library.git"
        }
    ]
}
```

En développement local, tu peux pointer vers un chemin local pour voir tes modifications sans re-tag à chaque fois :

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-media-library",
            "options": { "symlink": true }
        }
    ]
}
```

### 2. Authentification (dépôt privé)

Composer te demandera un token GitHub au premier `require`. Crée un **fine-grained personal access token** :

1. https://github.com/settings/personal-access-tokens/new
2. Repository access → *Only select repositories* → `laravel-media-library`
3. Permissions → *Repository permissions* → `Contents: Read-only`
4. Colle le token quand Composer le demande — il est stocké dans `~/.composer/auth.json` et réutilisé automatiquement ensuite.

### 3. Installer le package

```bash
composer require skaldoe/laravel-media-library
# ou en dev local :
composer require skaldoe/laravel-media-library:@dev
```

### 4. Publier la configuration (optionnel)

```bash
php artisan vendor:publish --tag=media-library-config
```

Génère `config/media-library.php` dans le projet hôte. Sans cette étape, les valeurs par défaut du package s'appliquent.

### 5. Migrer

```bash
php artisan migrate
```

Le package charge automatiquement ses migrations (`media`, `media_folders`, `media_attachments`) via `loadMigrationsFrom()` — aucun fichier à copier dans le projet hôte.

> ⚠️ Si le projet avait déjà des migrations locales pour ces tables (avant l'extraction en package), supprime-les de `database/migrations/` du projet hôte pour éviter un conflit "table already exists".

## Configuration

```php
// config/media-library.php
return [
    // Préfixe des routes générées : /api/{route_prefix}/medias
    'route_prefix' => 'admin',

    // Middleware appliqué au groupe de routes (SubstituteBindings est
    // toujours ajouté automatiquement par le package, inutile de le
    // déclarer ici)
    'middleware' => ['auth:sanctum'],

    // Disque Laravel utilisé pour stocker les fichiers
    'disk' => 'public',

    // Règles d'upload
    'accepted_mimes' => 'jpeg,jpg,png,gif,webp,svg',
    'max_file_size' => 2048, // Ko

    // Modèle User pour la relation uploaded_by.
    // null = reprend automatiquement auth.providers.users.model
    'user_model' => null,
];
```

## Routes exposées

| Méthode | URI | Action |
|---|---|---|
| GET | `/api/{prefix}/medias` | Liste paginée des médias + dossiers du niveau courant |
| POST | `/api/{prefix}/medias` | Upload de un ou plusieurs fichiers |
| PUT | `/api/{prefix}/medias/{media}` | Déplacer un média vers un autre dossier |
| DELETE | `/api/{prefix}/medias/{media}` | Supprimer un média (refusé s'il est attaché) |
| GET | `/api/{prefix}/folders` | Arbre des dossiers |
| POST | `/api/{prefix}/folders` | Créer un dossier |
| PUT | `/api/{prefix}/folders/{folder}` | Renommer / déplacer un dossier |
| DELETE | `/api/{prefix}/folders/{folder}` | Supprimer un dossier |

## Utilisation dans un modèle

Ajoute le trait `HasMedia` à n'importe quel modèle Eloquent :

```php
use SkalDoe\MediaLibrary\Concerns\HasMedia;

class Capsule extends Model
{
    use HasUuids, HasFactory, SoftDeletes, HasMedia;
}
```

### Média unique par collection (ex. avatar, thumbnail)

```php
public function thumbnailAttachment()
{
    return $this->singleMediaAttachment('capsule-thumbnail')->with('media');
}

public function getThumbnailAttribute()
{
    return $this->thumbnailAttachment?->media;
}
```

Pour assigner/remplacer :

```php
$capsule->syncMedia($mediaId, 'capsule-thumbnail');
```

### Collection multiple (galerie)

```php
$capsule->medias(); // MorphToMany vers tous les médias attachés, toutes collections confondues
$capsule->medias()->wherePivot('collection_name', 'gallery')->get();
```

### Eager loading

Toujours charger la relation d'attachement explicitement plutôt que de compter sur `$with` global (évite le N+1 silencieux) :

```php
Capsule::with('thumbnailAttachment.media')->paginate();
```

## Dépannage

**"Attempt to read property on string" dans une FormRequest** → le middleware `SubstituteBindings` n'est pas actif sur le groupe de routes. Le `ServiceProvider` du package l'ajoute automatiquement ; si l'erreur persiste après mise à jour du package, vide le cache : `php artisan route:clear && php artisan config:clear`.

**Suppression/déplacement "réussit" sans effet visible** → même cause que ci-dessus : sans binding de route, Laravel injecte une instance vide du modèle au lieu de résoudre le bon enregistrement.

**Erreur 404 lors de `composer require`** → vérifie que le token GitHub a bien accès à ce repo précis (`Repository access` dans les settings du token) et qu'il n'a pas expiré.

## Versionning

Le package suit [SemVer](https://semver.org). Fixe une contrainte de version explicite dans chaque projet consommateur (`^0.1`, pas `@dev`) une fois stabilisé, pour éviter qu'une évolution sur un projet ne casse silencieusement les autres.