# Règles — DTOs (Data Transfer Objects)

## Principe

Chaque frontière de couche (HTTP → Action, Action → Repository, Repository → Action) passe par un objet **typé et immuable**. On ne balade jamais un `array` non documenté ni un `Illuminate\Http\Request` au-delà du Controller.

## Outil recommandé

Utiliser le package `spatie/laravel-data` quand disponible dans le projet (unifie DTO + validation + transformation). Sinon, DTO natif en `readonly class` (PHP 8.2+).

### Avec spatie/laravel-data

```php
<?php

namespace App\Domains\Billing\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Min;

final class PlaceOrderData extends Data
{
    public function __construct(
        public readonly int $customerId,
        public readonly array $lines,
        #[Min(0)]
        public readonly int $shippingMethodId,
    ) {}
}
```

### DTO natif (sans dépendance externe)

```php
<?php

namespace App\Domains\Billing\DataTransferObjects;

final readonly class PlaceOrderData
{
    public function __construct(
        public int $customerId,
        public array $lines,
        public int $shippingMethodId,
    ) {}

    public static function fromRequest(PlaceOrderRequest $request): self
    {
        return new self(
            customerId: $request->integer('customer_id'),
            lines: $request->array('lines'),
            shippingMethodId: $request->integer('shipping_method_id'),
        );
    }
}
```

## Règles

1. **Toujours `readonly`** — un DTO ne se modifie pas après construction. S'il faut une variante, en créer une nouvelle instance (`with()` pattern si besoin).
2. **Nom de classe** : `{Contexte}Data` (ex: `PlaceOrderData`, `OrderSummaryData`). Pas de suffixe `DTO` (redondant avec le namespace `DataTransferObjects`).
3. **Un DTO ne contient jamais de logique métier** — uniquement structure de données + éventuellement des règles de validation déclaratives et un factory `fromRequest()`/`fromArray()`.
4. **La validation métier "dure"** (règles qui nécessitent la base de données, ex: "ce customer_id existe et est actif") reste dans le Form Request (`07-api-http.md`), pas dans le DTO — le DTO valide la forme, le Request valide le fond.
5. **Un DTO par cas d'usage**, pas un DTO générique réutilisé partout. `PlaceOrderData` ≠ `UpdateOrderData` même si les champs se recoupent — évite les champs `nullable` qui ne le sont que dans certains contextes.
6. Les DTOs de sortie (retour d'une Query, par exemple) suivent la même règle : `OrderSummaryData`, `readonly`, un DTO par forme de résultat.

## Ce qui n'est PAS un DTO

- Un Eloquent Model n'est pas un DTO — ne jamais le retourner tel quel à travers une frontière HTTP (voir `07-api-http.md` sur les API Resources).
- Un tableau associatif "libre" n'est pas un DTO — s'il traverse plus d'une méthode, il doit devenir un DTO typé.
