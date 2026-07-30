---
name: laravel-architecture
description: Vue d'ensemble de l'architecture orientée services de cette app Laravel (Controller → Service → Repository → Model). Consulter avant d'ajouter une feature métier, un endpoint, ou de créer une nouvelle classe dans app/.
---

Cette app suit une architecture en couches strictes. Chaque couche ne parle qu'à la couche immédiatement en dessous.

```
Controller (HTTP/Inertia, fin)
    → FormRequest (validation)
    → Service (app/Services) — logique métier
        → Repository Contract (app/Repositories/Contracts)
            → Repository (app/Repositories) — persistence Eloquent
                → Model (app/Models)
    → Resource (app/Http/Resources) — transforme le Model avant de le renvoyer en props Inertia
```

Règles non négociables :
- Un Controller n'appelle jamais Eloquent directement (`User::create()`, `$model->save()`, etc.). Il délègue à un Service.
- Un Service ne dépend jamais d'un Repository concret, seulement de son contrat (interface). Binding dans `App\Providers\RepositoryServiceProvider`.
- Un Controller ne renvoie jamais un Model brut en prop Inertia — toujours via une classe `Resource`.
- Les règles métier violées lèvent une exception dédiée (`App\Exceptions\DomainException`), pas un `abort()` ou un `if` silencieux dans le Controller.

Exemple canonique dans ce repo (auth Breeze refactorée) : `app/Services/UserService.php`, `app/Repositories/UserRepository.php` + `app/Repositories/Contracts/UserRepositoryContract.php`, `app/Http/Resources/UserResource.php`, `app/Http/Controllers/Auth/RegisteredUserController.php`. Reproduire ce même découpage pour toute nouvelle entité métier.

Skills liés, à charger selon la couche travaillée :
- `laravel-service-layer` — écrire un Service
- `laravel-repository-pattern` — écrire un Repository + Contract
- `laravel-inertia-vue` — Controller/props/pages Vue
- `laravel-authz-policies` — autorisation
- `laravel-exceptions` — exceptions métier
- `laravel-testing-pest` — tester tout ça
- `laravel-code-style` — conventions PHP transverses (strict_types, final, Pint)
