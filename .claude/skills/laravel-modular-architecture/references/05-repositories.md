# Règles — Repositories

## Principe

Les Actions et Queries ne parlent jamais directement à Eloquent (`Model::query()`) — elles passent par une interface de Repository, bindée dans le `ServiceProvider` du domaine. Ça permet de mocker en test et de changer la source de données sans toucher aux Actions.

## Squelette obligatoire

```php
<?php

namespace App\Domains\Billing\Repositories\Contracts;

use App\Domains\Billing\DataTransferObjects\PlaceOrderData;
use App\Domains\Billing\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function find(int $id): ?Order;

    public function create(PlaceOrderData $data): Order;

    public function forCustomer(int $customerId): Collection;
}
```

```php
<?php

namespace App\Domains\Billing\Repositories;

use App\Domains\Billing\DataTransferObjects\PlaceOrderData;
use App\Domains\Billing\Models\Order;
use App\Domains\Billing\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function find(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function create(PlaceOrderData $data): Order
    {
        return Order::query()->create([
            'customer_id' => $data->customerId,
            'shipping_method_id' => $data->shippingMethodId,
        ]);
    }

    public function forCustomer(int $customerId): Collection
    {
        return Order::query()->where('customer_id', $customerId)->get();
    }
}
```

## Règles

1. **Une interface par agrégat métier**, pas par table — un `OrderRepositoryInterface` peut couvrir `Order` + ses `OrderLines` si elles n'ont pas de sens en dehors d'une commande.
2. **Binding dans le `{Domain}ServiceProvider`**, jamais dans `AppServiceProvider` :
```php
$this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
```
3. **Le Repository ne contient pas de logique métier** — uniquement accès aux données (requêtes, création, mise à jour). Toute décision métier reste dans l'Action.
4. **Pas de Repository générique/abstrait type `BaseRepository` avec CRUD magique** — préférer des méthodes explicites et nommées par intention (`forCustomer()`, `pendingSince()`) plutôt que `findBy(array $criteria)` qui masque ce qui est réellement interrogé.
5. **Le Model Eloquent reste fin** : relations, casts, scopes locaux simples uniquement. Pas de requêtes complexes multi-tables dans le Model — ça va dans le Repository.
6. **Query Builder complexe ou raw SQL** : autorisé dans le Repository si justifié par la performance, mais isolé et commenté.

## Quand NE PAS créer de Repository

Pour un Model trivial en lecture seule sans logique de requête complexe et sans besoin de mock en test (ex: table de référence statique consultée directement dans une Query dédiée), il est acceptable d'utiliser Eloquent directement dans une Query — ne pas sur-architecturer. Documenter ce choix en commentaire si un développeur pourrait s'attendre à un Repository par cohérence avec le reste du domaine.
