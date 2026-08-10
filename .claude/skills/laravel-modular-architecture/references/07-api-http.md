# Règles — Couche API / HTTP

## Principe

Le Controller est un **chef d'orchestre mince** : il valide (délègue au Form Request), construit un DTO, appelle une Action ou une Query, retourne une Resource. Il ne contient jamais de logique métier.

## Squelette obligatoire du Controller

```php
<?php

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Billing\Actions\PlaceOrderAction;
use App\Domains\Billing\DataTransferObjects\PlaceOrderData;
use App\Domains\Billing\Http\Requests\PlaceOrderRequest;
use App\Domains\Billing\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;

final class OrderController
{
    public function store(PlaceOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->execute(PlaceOrderData::fromRequest($request));

        return OrderResource::make($order)
            ->response()
            ->setStatusCode(201);
    }
}
```

## Règles

### Form Requests

1. **Une Request par endpoint**, jamais réutilisée entre `store` et `update` si les règles diffèrent.
2. `authorize()` délègue à une Policy (`Domains/{Nom}/Policies`), jamais `return true;` en dur sauf endpoint public assumé.
3. Les règles de validation qui nécessitent la DB (`exists:`, `unique:`) restent ici, pas dans le DTO.

```php
public function rules(): array
{
    return [
        'customer_id' => ['required', 'integer', 'exists:customers,id'],
        'lines' => ['required', 'array', 'min:1'],
        'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
        'shipping_method_id' => ['required', 'integer'],
    ];
}
```

### API Resources — obligatoires, sans exception

**Jamais** de Model Eloquent, de `Model::all()` ou `Model::get()` retourné brut dans une réponse JSON. Toujours passer par une Resource :

```php
<?php

namespace App\Domains\Billing\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => $this->total,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

Raison : ça évite de fuiter des colonnes internes (hash de mot de passe, clés étrangères techniques, timestamps de soft-delete...) et ça découpe le contrat API du schéma DB — le schéma peut évoluer sans casser l'API.

### Versionning

1. Toutes les routes API sous `api/v{n}/...` **dès le départ**, même en v1 unique — ne jamais démarrer sans préfixe de version.
2. Une nouvelle version majeure d'un domaine = nouveau dossier `Http/Controllers/V2/` + `Resources/V2/` dans le même domaine, pas un domaine dupliqué.
3. Ne jamais casser une Resource existante en production — ajouter des champs est acceptable, en retirer/renommer nécessite une nouvelle version.

### Authentification API

- SPA interne (même organisation) → **Sanctum** (cookies + CSRF).
- API consommée par des tiers/mobile → **Sanctum tokens** ou **Passport** si OAuth2 complet nécessaire (scopes, clients tiers).
- Ne jamais gérer l'auth "à la main" via un champ API-key custom sauf besoin très spécifique documenté.

### Codes de statut et erreurs

1. Utiliser les codes HTTP sémantiquement corrects (201 création, 204 suppression sans contenu, 422 validation, 404 introuvable, 409 conflit métier).
2. Centraliser le format d'erreur JSON via un `Handler`/`ExceptionRenderer` unique, cohérent sur toute l'API — jamais de format d'erreur différent selon le domaine.

```json
{
  "message": "The given data was invalid.",
  "errors": { "customer_id": ["The customer id field is required."] }
}
```

## Anti-patterns à corriger

- `return Order::all();` dans un Controller → toujours `OrderResource::collection(...)`.
- Logique métier (calculs, conditions business) écrite directement dans le Controller → extraire en Action.
- Validation faite "à la main" avec des `if` dans le Controller au lieu d'un Form Request.
