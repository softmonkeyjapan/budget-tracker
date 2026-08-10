# Règles — Conventions de nommage

Tableau de référence rapide. En cas de doute, chercher un exemple existant dans le même domaine avant d'inventer une nouvelle convention.

| Type de classe | Convention | Exemple |
|---|---|---|
| Domaine | Nom métier, PascalCase, singulier ou pluriel cohérent | `Billing`, `Catalog`, `Shipping` |
| Action | `{Verbe}{Complément}Action` | `PlaceOrderAction`, `CancelOrderAction`, `RefundInvoiceAction` |
| Query (lecture dédiée) | `{Get/List/Find}{Complément}Query` | `GetOrderSummaryQuery`, `ListPendingInvoicesQuery` |
| DTO | `{Contexte}Data` | `PlaceOrderData`, `OrderSummaryData` |
| Model Eloquent | Nom métier singulier | `Order`, `Invoice`, `ShippingMethod` |
| Repository interface | `{Agrégat}RepositoryInterface` | `OrderRepositoryInterface` |
| Repository implémentation | `Eloquent{Agrégat}Repository` | `EloquentOrderRepository` |
| Event | Verbe au passé | `OrderPlaced`, `InvoicePaid`, `ShipmentDispatched` |
| Listener | `{Effet}When{Event}` | `CreateShipmentWhenOrderPlaced` |
| Controller | `{Ressource}Controller` (au singulier) | `OrderController`, pas `OrdersController` |
| Form Request | `{Verbe}{Ressource}Request` | `PlaceOrderRequest`, `UpdateOrderRequest` |
| API Resource | `{Ressource}Resource` | `OrderResource` |
| Policy | `{Ressource}Policy` | `OrderPolicy` |
| ServiceProvider de domaine | `{Domaine}ServiceProvider` | `BillingServiceProvider` |
| Interface partagée | `{Capacité}Interface` | `PaymentGatewayInterface`, `StockAvailabilityInterface` |
| Value Object | Nom du concept, sans suffixe | `Money`, `EmailAddress` |
| Test Unit | `{ClasseTestée}Test` dans `tests/Unit/Domains/{Domaine}/...` | `PlaceOrderActionTest` |
| Test Feature | Nom de l'endpoint/comportement | `PlaceOrderTest` dans `tests/Feature/Domains/{Domaine}/Http/` |

## Routes

- Toujours préfixées `api/v{n}/{ressource-en-kebab-case}`.
- Nom de route : `api.v{n}.{domaine}.{action}` (ex: `api.v1.billing.orders.store`).
- Ressource en kebab-case et au pluriel dans l'URL (`/api/v1/orders`), même si le Controller et le Model sont au singulier.

## Fichiers vs classes

- Un fichier = une classe publique, nom de fichier identique au nom de classe (PSR-4 standard).
- Pas d'abréviations obscures (`Ord` pour `Order`) — le nom complet du concept métier prime sur la brièveté.

## Ce qu'il ne faut jamais nommer ainsi

- `XxxService`, `XxxManager`, `XxxHelper`, `XxxUtil` — ces noms signalent une classe fourre-tout ; toujours préférer un nom d'intention précis (Action, Query, ou Value Object selon le cas).
- `BaseRepository`, `AbstractController` génériques avec logique magique — préférer l'explicite à l'héritage profond.
