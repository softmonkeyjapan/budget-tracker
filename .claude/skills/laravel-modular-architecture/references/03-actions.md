# Règles — Actions (remplace les "Services" génériques)

## Principe

Une **Action = un seul cas d'usage métier**, une seule méthode publique d'exécution. Interdiction de créer une classe `XxxService` avec plusieurs méthodes non liées — c'est le anti-pattern que cette architecture élimine.

## Squelette obligatoire

```php
<?php

namespace App\Domains\Billing\Actions;

use App\Domains\Billing\DataTransferObjects\PlaceOrderData;
use App\Domains\Billing\Models\Order;
use App\Domains\Billing\Repositories\Contracts\OrderRepositoryInterface;
use App\Domains\Shared\Contracts\PaymentGatewayInterface;

final class PlaceOrderAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly PaymentGatewayInterface $gateway,
    ) {}

    public function execute(PlaceOrderData $data): Order
    {
        // logique métier, une seule responsabilité
    }
}
```

## Règles

1. **Nom de classe** : `{Verbe}{Complément}Action` (ex: `PlaceOrderAction`, `CancelOrderAction`, `RefundInvoiceAction`). Jamais `OrderService`, `OrderManager`, `OrderHelper`.
2. **Une seule méthode publique** : `execute()` par convention (accepter `handle()` si le projet existant utilise déjà cette convention — rester cohérent avec l'existant plutôt qu'imposer `execute`).
3. **`final class`** par défaut — une Action n'est pas conçue pour être étendue ; si besoin de variantes, composer plusieurs Actions plutôt qu'hériter.
4. **Dépendances injectées via le constructeur**, typées par interface (`Repositories/Contracts`, `Shared/Contracts`), jamais par implémentation concrète directement — permet le mock en test.
5. **Entrée typée** : l'Action reçoit un DTO (`04-dto-data.md`), jamais un `Request` Laravel ni un `array` brut. Ça découple l'Action de la couche HTTP — elle doit pouvoir être appelée depuis un Job, une commande Artisan, ou un test, sans HTTP.
6. **Sortie typée** : retourner un Model, un DTO, ou `void` — jamais un `array` non documenté.
7. **Pas d'accès direct à `request()` ou `auth()`** dans l'Action — ces informations doivent être passées explicitement dans le DTO d'entrée par le Controller appelant.
8. **Transactions DB** : si l'Action touche plusieurs tables/agrégats, wrapper dans `DB::transaction()` à l'intérieur de l'Action elle-même (pas dans le Controller).

```php
public function execute(PlaceOrderData $data): Order
{
    return DB::transaction(function () use ($data) {
        $order = $this->orders->create($data);
        $this->gateway->charge($data->paymentMethod, $order->total);
        event(new OrderPlaced($order->id));

        return $order;
    });
}
```

## Séparation lecture / écriture (CQRS léger — optionnel)

Pour les domaines à fort trafic en lecture, séparer :
- `Actions/` → toutes les opérations d'écriture (create/update/delete/side-effects)
- `Queries/` → objets de lecture dédiés, optimisés indépendamment (peuvent bypasser le Repository et requêter directement si besoin de perf)

```php
final class GetOrderSummaryQuery
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function execute(int $orderId): OrderSummaryData
    {
        // requête optimisée, potentiellement dénormalisée
    }
}
```

Ne pas imposer ce découpage par défaut — seulement si le domaine a un vrai besoin de perf en lecture différencié. Ne pas confondre avec du CQRS complet (event sourcing) qui est hors périmètre de cette skill.

## Anti-patterns à corriger si rencontrés dans le code existant

- Une classe `OrderService` avec 15 méthodes → scinder en 15 Actions.
- Une Action qui appelle une autre Action directement en dur → envisager composition explicite (une Action orchestratrice appelle plusieurs Actions plus fines via injection), ou repenser si ça devrait être un Event.
- Logique métier dans le Controller → extraire en Action.
