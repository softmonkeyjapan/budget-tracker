# Règles — Créer un nouveau domaine (module)

Quand l'utilisateur demande de créer un nouveau domaine/module, suivre ces étapes dans l'ordre. Ne pas générer de code métier tant que le squelette n'est pas posé.

## Étape 1 — Squelette de dossiers

Créer, pour un domaine `Nom` :

```
app/Domains/Nom/
├── Actions/
├── Models/
├── DataTransferObjects/
├── Events/
├── Listeners/
├── Http/Controllers/
├── Http/Requests/
├── Http/Resources/
├── Policies/
├── Repositories/Contracts/
├── Providers/
└── routes.php
```

## Étape 2 — ServiceProvider de domaine

```php
<?php

namespace App\Domains\Nom\Providers;

use App\Domains\Nom\Repositories\Contracts\NomRepositoryInterface;
use App\Domains\Nom\Repositories\EloquentNomRepository;
use Illuminate\Support\ServiceProvider;

class NomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NomRepositoryInterface::class, EloquentNomRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../../../../database/migrations/nom');
    }
}
```

Enregistrer ce provider dans `bootstrap/providers.php` (Laravel 11+) ou `config/app.php` (Laravel ≤10).

## Étape 3 — routes.php du domaine

```php
<?php

use App\Domains\Nom\Http\Controllers\NomController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/nom')->name('api.v1.nom.')->group(function () {
    Route::get('/', [NomController::class, 'index'])->name('index');
    Route::post('/', [NomController::class, 'store'])->name('store');
});
```

## Étape 4 — Contenu métier

Une fois le squelette posé, générer le contenu au cas par cas en suivant les règles des autres fichiers de référence :
- Models Eloquent → `05-repositories.md` (le Model reste fin, la logique de requête va dans le Repository)
- Actions → `03-actions.md`
- DTOs / Requests → `04-dto-data.md`
- Controllers / Resources → `07-api-http.md`
- Events/Listeners si le domaine doit notifier d'autres domaines → `06-events-communication.md`

## Étape 5 — Ne jamais oublier

- Un test au minimum par Action créée (`08-tests.md`).
- Vérifier qu'aucune classe de ce domaine n'importe directement une classe d'un autre domaine (sauf `Shared`) — sinon, voir `06-events-communication.md`.
