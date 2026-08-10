# Règles — Structure de dossiers

## Arborescence cible

```
app/
├── Domains/
│   ├── Billing/
│   │   ├── Actions/
│   │   ├── Models/
│   │   ├── DataTransferObjects/
│   │   ├── Events/
│   │   ├── Listeners/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Policies/
│   │   ├── Repositories/
│   │   │   ├── Contracts/          (interfaces)
│   │   │   └── EloquentXxxRepository.php
│   │   ├── Providers/
│   │   │   └── BillingServiceProvider.php
│   │   ├── Queries/                (lecture, si besoin — voir 03-actions.md)
│   │   └── routes.php
│   ├── Catalog/
│   ├── Shipping/
│   └── Shared/
│       ├── Contracts/              (interfaces utilisées par plusieurs domaines)
│       ├── Events/                 (events "globaux" type UserRegistered)
│       └── ValueObjects/           (Money, Email, etc. — sans dépendance métier)
├── Http/
│   └── Middleware/                 (middleware transverses uniquement)
├── Providers/
│   └── AppServiceProvider.php      (ne fait qu'enregistrer les providers de domaine)
└── Console/
```

## Règles strictes

1. **Un dossier `Domains/{Nom}` par domaine métier**, jamais par couche technique globale (pas de `app/Services/`, `app/Repositories/` au niveau racine).
2. `app/Domains/Shared` contient **uniquement** ce qui est légitimement partagé par ≥2 domaines : interfaces, Value Objects sans dépendance, events globaux. Si un doute existe, le code reste dans le domaine d'origine — il vaut mieux dupliquer un peu que sur-partager trop tôt.
3. `app/Http` racine reste minimal : middleware transverses (auth, cors, throttle). Les Controllers/Requests/Resources spécifiques à un domaine vivent dans `Domains/{Nom}/Http/`.
4. `app/Providers/AppServiceProvider.php` ne fait qu'appeler `register()`/`boot()` des providers de domaine — il ne contient jamais de logique métier ni de binding de domaine.
5. Chaque domaine a son propre fichier `routes.php`, chargé depuis `RouteServiceProvider` ou le `{Domain}ServiceProvider` :

```php
// app/Domains/Billing/Providers/BillingServiceProvider.php
public function boot(): void
{
    $this->loadRoutesFrom(__DIR__.'/../routes.php');
}
```

## Convention de nommage des domaines

- Nom du dossier au singulier ou pluriel cohérent avec le vocabulaire métier (`Billing`, `Catalog`, `Shipping` — pas `Billings`).
- Un domaine = un sous-système métier avec sa propre raison de changer (principe de responsabilité unique appliqué au niveau module, pas juste à la classe).
- Taille indicative : si un domaine dépasse ~15-20 Actions, envisager de le scinder en sous-domaines (ex: `Billing/Invoicing`, `Billing/Payments`).

## Quand créer un nouveau domaine vs. ajouter à un domaine existant

Créer un nouveau domaine si :
- Le concept a son propre cycle de vie métier (ex: `Order` vs `Shipment` vs `Invoice`).
- Une équipe différente pourrait en être propriétaire.
- On pourrait imaginer l'extraire en service séparé un jour.

Ajouter au domaine existant si :
- C'est un détail d'implémentation du même concept métier (ex: `RefundOrderAction` reste dans `Billing`, pas un nouveau domaine `Refunds`).

Voir `02-domaines-modules.md` pour le détail de ce que contient chaque sous-dossier d'un domaine.
