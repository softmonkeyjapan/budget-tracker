# Règles — Hybride Inertia/Vue + API (même architecture, deux façades)

## Principe

Le cœur de l'architecture (Domains, Actions, DTOs, Repositories, Events) est **strictement identique** que la sortie soit une vue Inertia/Vue ou une réponse JSON d'API. Ce qui change, c'est **uniquement la façade HTTP** : le Controller et ce qu'il retourne. Une Action ne sait jamais si elle a été appelée depuis une page Inertia ou un endpoint API consommé par un tiers — c'est précisément ce découplage qui permet de supporter les deux sans dupliquer la logique métier.

## Deux façades possibles par domaine

```
app/Domains/Billing/Http/
├── Controllers/
│   ├── Web/                    ← rend des pages Inertia
│   │   └── OrderController.php
│   └── Api/                    ← rend du JSON pur (API publique/mobile/tierce)
│       └── OrderController.php
├── Requests/                   ← partagées entre Web et Api si les règles sont identiques
├── Resources/                  ← utilisées par Api/, et par Web/ pour typer les props Inertia
```

**Règle de décision** : si l'application n'expose PAS d'API publique/tierce/mobile aujourd'hui, ne créez pas le dossier `Api/` par anticipation — restez avec `Http/Controllers/` à plat (façade Web unique). Créez `Web/` vs `Api/` seulement au moment où une vraie deuxième façade apparaît (même logique de "duplication d'abord" que pour `Shared`, voir `10-code-commun.md`).

## Controller Web (Inertia) — squelette

```php
<?php

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Billing\Actions\PlaceOrderAction;
use App\Domains\Billing\DataTransferObjects\PlaceOrderData;
use App\Domains\Billing\Http\Requests\PlaceOrderRequest;
use App\Domains\Billing\Http\Resources\OrderResource;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class OrderController
{
    public function create(): Response
    {
        return Inertia::render('Billing/Orders/Create');
    }

    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): RedirectResponse
    {
        $order = $action->execute(PlaceOrderData::fromRequest($request));

        return to_route('billing.orders.show', $order)
            ->with('success', 'Commande créée.');
    }

    public function show(int $orderId, OrderRepositoryInterface $orders): Response
    {
        return Inertia::render('Billing/Orders/Show', [
            'order' => OrderResource::make($orders->find($orderId)),
        ]);
    }
}
```

Points clés :
1. **Même Action, même DTO, même Repository** que dans `07-api-http.md` — rien ne change côté métier.
2. **Les API Resources restent utilisées** même côté Inertia : elles servent à typer et filtrer les props envoyées au front, exactement comme pour du JSON d'API. `OrderResource::make($order)` fonctionne aussi bien pour `Inertia::render(['order' => ...])` que pour une réponse JSON pure.
3. **`store`/`update`/`destroy` retournent des redirections** (`to_route()`, `redirect()`), pas du JSON — Inertia gère lui-même le rechargement de page côté front via `router.post()`. Ne jamais retourner un `OrderResource` brut depuis une action d'écriture Web ; utiliser `->with('success', ...)` pour les messages flash.
4. **Le Form Request est réutilisé tel quel** entre façade Web et Api si les règles de validation sont identiques — Inertia affiche automatiquement les erreurs de validation 422 dans les formulaires Vue via `useForm()`, aucun code de gestion d'erreur custom nécessaire.

## Controller Api — squelette identique à `07-api-http.md`

```php
final class OrderController
{
    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->execute(PlaceOrderData::fromRequest($request));

        return OrderResource::make($order)->response()->setStatusCode(201);
    }
}
```

Rien ne change par rapport à la règle existante — c'est la façade `Web/` qui est nouvelle, pas la façade `Api/`.

## Routes — deux fichiers si deux façades existent

```
app/Domains/Billing/routes.php       # web (Inertia), pas de préfixe /api
app/Domains/Billing/routes-api.php   # api/v1/..., si façade Api existe
```

```php
// routes.php
Route::middleware(['web', 'auth'])->prefix('billing')->name('billing.')->group(function () {
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});
```

Chargés séparément dans le `{Domain}ServiceProvider` :

```php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../routes.php');

    if (file_exists(__DIR__.'/../routes-api.php')) {
        $this->loadRoutesFrom(__DIR__.'/../routes-api.php');
    }
}
```

## Structure du front Vue — miroir de la structure des domaines

Le front suit le **même découpage par domaine** que le back, pas une structure `resources/js/Pages` + `resources/js/Components` plate et globale :

```
resources/js/
├── Domains/
│   ├── Billing/
│   │   ├── Pages/               ← composants Inertia (correspondent à Inertia::render('Billing/Orders/Create'))
│   │   │   └── Orders/
│   │   │       ├── Create.vue
│   │   │       └── Show.vue
│   │   ├── Components/          ← composants réutilisés uniquement à l'intérieur de ce domaine
│   │   │   └── OrderSummaryCard.vue
│   │   ├── composables/         ← logique réactive spécifique au domaine (usePlaceOrderForm.ts)
│   │   └── types.ts             ← types TypeScript miroir des Resources PHP de ce domaine
│   ├── Catalog/
│   └── Shipping/
├── Shared/
│   ├── Components/               ← composants génériques UI (Button, Modal, Table) sans sémantique métier
│   ├── Layouts/
│   └── composables/
└── app.ts
```

**Règle** : `Inertia::render('Billing/Orders/Create')` correspond au fichier `resources/js/Domains/Billing/Pages/Orders/Create.vue` — configurer le resolver Inertia en conséquence :

```ts
// app.ts
createInertiaApp({
  resolve: (name) => resolvePageComponent(
    `./Domains/${name.split('/').slice(0, -1).join('/')}/Pages/${name.split('/').slice(-1)}.vue`,
    import.meta.glob('./Domains/**/Pages/**/*.vue'),
  ),
  // ...
})
```

**Le composant Vue reçoit les props déjà typées et filtrées par l'`OrderResource` PHP** — jamais un Model brut sérialisé. Le contrat entre back et front est le même que pour une API, seule la façon de le livrer (props Inertia vs JSON fetch) diffère.

## Data partagée entre pages (`HandleInertiaRequests`)

Les données globales (utilisateur connecté, notifications flash, permissions) passent par le middleware `HandleInertiaRequests::share()`, **jamais dupliquées manuellement dans chaque Controller** :

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? UserResource::make($request->user()) : null,
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
        ],
    ];
}
```

Un domaine ne doit jamais avoir besoin d'ajouter ses propres données à ce middleware global pour un besoin qui lui est spécifique — s'il a un besoin propre, ça passe par les props de la page (`Inertia::render` avec des données du domaine), pas par le partage global.

## Choisir Web vs Api pour un nouvel endpoint

```
Le consommateur est-il le front Vue de cette même application (via Inertia) ?
├── Oui → Controller dans Http/Controllers/ (ou Http/Controllers/Web/ si Api/ existe déjà), retourne Inertia::render() ou redirect()
└── Non — mobile natif, app tierce, webhook entrant
    └── Controller dans Http/Controllers/Api/, retourne JsonResponse via Resource
```

## Anti-patterns à corriger

- Un Controller Inertia qui retourne un `Model` Eloquent brut en prop au lieu d'une Resource → le front reçoit des colonnes internes non filtrées.
- Dupliquer une Action ou une Query différemment "pour Inertia" et "pour l'API" → il n'existe qu'une seule Action ; seule la façade Controller change.
- Composants Vue génériques métier (ex: `OrderSummaryCard.vue`) rangés dans `resources/js/Shared/Components` par facilité → s'il a une sémantique métier propre à un domaine, il reste dans `Domains/{Nom}/Components`, même règle que le code commun back (`10-code-commun.md`).
- Créer la façade `Api/` par anticipation dans un projet qui ne sert que de l'Inertia aujourd'hui → attendre le besoin constaté.
