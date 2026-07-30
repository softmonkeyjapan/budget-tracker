---
name: laravel-repository-pattern
description: Convention pour écrire un Repository + son Contract (app/Repositories) dans cette app Laravel. Utiliser à chaque nouvelle entité/Model métier qui a besoin d'être créée/modifiée/supprimée depuis un Service.
---

Chaque Model métier a un Repository qui encapsule ses accès Eloquent, et un Contract (interface) dont dépendent les Services. Exemple de référence : `app/Repositories/Contracts/UserRepositoryContract.php` + `app/Repositories/UserRepository.php`.

Règles :
- Contract dans `app/Repositories/Contracts/XxxRepositoryContract.php` (interface pure, pas de logique).
- Implémentation dans `app/Repositories/XxxRepository.php`, `final class XxxRepository implements XxxRepositoryContract`, `declare(strict_types=1)`.
- Binder chaque nouveau contrat dans `app/Providers/RepositoryServiceProvider.php` (propriété `$bindings`) — sinon l'injection dans le Service échoue.
- Méthodes typées : paramètres `array<string, mixed> $data`, retour `Model`, `?Model`, `Collection`, ou `void`. Jamais de retour `Builder`/`Query` (le Repository exécute la requête, il ne la fuit pas).
- Le Repository ne contient aucune règle métier (pas de `if` conditionnant un comportement business) — uniquement des opérations de persistence. La règle métier vit dans le Service qui l'appelle.
- Pas de couche `Repository` générique/abstraite partagée entre tous les Models — un Repository par Model, explicite, avec des méthodes nommées pour ce Model précis (pas de `find()`/`all()` génériques type ORM-sur-ORM).

Cette app assume volontairement le Repository comme couche supplémentaire au-dessus d'Eloquent (décision explicite, pas le défaut Laravel) : le bénéfice recherché est de garder les Services testables sans base de données réelle (mock du Contract) et de centraliser les accès Eloquent d'une entité en un seul endroit.
