# Règles — Communication inter-domaines

## Principe

Un domaine ne référence **jamais** directement une classe concrète (Action, Repository, Model) d'un autre domaine. Deux mécanismes autorisés seulement :

1. **Events** — pour "informer" un ou plusieurs domaines qu'un fait s'est produit (préféré, asynchrone possible).
2. **Interfaces partagées dans `Domains/Shared/Contracts`** — pour un besoin d'appel synchrone incontournable (ex: `PaymentGatewayInterface` utilisé par `Billing`, implémenté quelque part de neutre).

## Events — squelette obligatoire

```php
<?php

namespace App\Domains\Billing\Events;

final class OrderPlaced
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $customerId,
    ) {}
}
```

Déclenché depuis l'Action :

```php
event(new OrderPlaced($order->id, $order->customer_id));
```

Écouté depuis un AUTRE domaine, dans son propre dossier `Listeners/` :

```php
<?php

namespace App\Domains\Shipping\Listeners;

use App\Domains\Billing\Events\OrderPlaced;
use App\Domains\Shipping\Actions\CreateShipmentAction;

final class CreateShipmentWhenOrderPlaced
{
    public function __construct(private readonly CreateShipmentAction $createShipment) {}

    public function handle(OrderPlaced $event): void
    {
        $this->createShipment->execute($event->orderId);
    }
}
```

Enregistrement dans le `ShippingServiceProvider` (pas dans `EventServiceProvider` global, pour garder l'enregistrement proche du domaine consommateur) :

```php
protected $listen = [
    OrderPlaced::class => [CreateShipmentWhenOrderPlaced::class],
];
```

## Règles

1. **Le domaine émetteur ne sait pas qui écoute.** `Billing` déclenche `OrderPlaced` sans savoir que `Shipping` ou `Notifications` réagissent — zéro import croisé.
2. **Nom des events au passé** (`OrderPlaced`, `InvoicePaid`, `ShipmentDispatched`) — un event constate un fait accompli, il ne commande pas une action.
3. **Un event transporte des données primitives ou des IDs**, jamais un Eloquent Model complet (évite les problèmes de sérialisation en queue et le couplage fort).
4. **Listener = un seul domaine réagit par listener.** Si trois domaines doivent réagir au même event, créer trois listeners (un par domaine consommateur), chacun dans son propre dossier `Listeners/`.
5. **Queue les listeners qui font des I/O lourds** (`implements ShouldQueue`) pour ne pas ralentir la requête HTTP d'origine.

## Interfaces partagées — quand les utiliser

Réservé aux cas où un appel **synchrone** est indispensable (ex: vérifier un solde avant de valider une commande) :

```php
// app/Domains/Shared/Contracts/StockAvailabilityInterface.php
interface StockAvailabilityInterface
{
    public function isAvailable(int $productId, int $quantity): bool;
}
```

`Billing` dépend de l'interface, jamais de `App\Domains\Catalog\Actions\CheckStockAction` directement. L'implémentation est bindée dans le provider du domaine qui la fournit (`Catalog`), et le binding est exposé/documenté pour que `Billing` puisse l'injecter.

## Anti-patterns à corriger

- `use App\Domains\Catalog\Models\Product;` dans une classe du domaine `Billing` → remplacer par un DTO/interface partagée.
- Un domaine qui appelle `app(SomeOtherDomainAction::class)->execute()` directement → remplacer par un Event si c'est une notification de fait, ou par une interface `Shared` si c'est un besoin synchrone légitime.
