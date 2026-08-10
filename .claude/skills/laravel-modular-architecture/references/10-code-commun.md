# Règles — Code commun (Support, Shared, packages internes)

## Principe

Le code commun est le point qui fait le plus souvent dériver une architecture modulaire vers le chaos : sans règle stricte, tout le monde y balance "ce qui pourrait servir ailleurs", et le module partagé devient un god-module plus gros que n'importe quel domaine métier — exactement le problème que le découpage par domaine était censé éviter.

Distinguer **trois natures** de code commun, qui ne vont jamais au même endroit.

## 1. Code commun technique → `app/Support/`

Ce qui relève de l'infrastructure applicative, sans aucune connaissance métier : formatters, exceptions de base, réponses HTTP standardisées, base classes techniques.

```
app/
├── Support/
│   ├── Http/
│   │   ├── ApiResponse.php        # wrapper de réponse standard
│   │   └── BaseFormRequest.php    # comportement commun (ex: format d'erreur)
│   ├── Exceptions/
│   │   └── DomainException.php    # exception de base que les domaines étendent
│   └── ValueObjects/              # uniquement si vraiment techniques
```

**Test à appliquer** : si retirer ce fichier casserait la compréhension d'un domaine métier précis, il n'a rien à faire dans `Support` — il appartient à ce domaine.

Exemple d'exception de domaine étendant la base technique :

```php
<?php

namespace App\Domains\Billing\Exceptions;

use App\Support\Exceptions\DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(int $productId)
    {
        parent::__construct("Product {$productId} is out of stock.");
    }
}
```

## 2. Code commun métier → `app/Domains/Shared/`

### Ce qui a le droit d'y entrer

- **Interfaces** de communication inter-domaines (`PaymentGatewayInterface`, `StockAvailabilityInterface`) — voir `06-events-communication.md`.
- **Value Objects sans dépendance métier** : `Money`, `EmailAddress`, `Percentage` — des concepts qui n'appartiennent à aucun domaine en propre.
- **Events globaux** concernant l'application entière dès le départ (`UserRegistered` si plusieurs domaines y réagissent nativement).

### Ce qui n'a PAS le droit d'y entrer

- Une classe métier placée là "parce qu'elle sera sûrement réutilisée un jour" — la duplication anticipée est un mal nécessaire, la sur-abstraction anticipée est pire.
- Un Model Eloquent partagé entre domaines. Si `Order` et `Invoice` ont tous les deux besoin du concept `Customer`, c'est le signal que `Customer` mérite son propre domaine (`Domains/Customers`), pas une place dans `Shared`.
- Un Repository ou une Action générique.
- Toute classe avec un nom vague type `Helper`, `Util`, `Common` — si un fichier de `Shared` a besoin d'un tel nom, c'est qu'il n'a pas de raison d'être claire d'exister à cet endroit.

### Règle de promotion — "duplication d'abord, abstraction ensuite"

Le code commun métier **ne naît jamais directement dans `Shared`**. Il naît dupliqué dans un domaine, et n'est promu vers `Shared` que lorsque la duplication est **constatée** (≥2 domaines en ont réellement besoin aujourd'hui), jamais parce qu'elle est anticipée ("pourrait servir un jour").

C'est le principe du **rule of three** : la première fois qu'un besoin apparaît dans un domaine, il reste dans ce domaine. La deuxième fois qu'il apparaît ailleurs, dupliquer encore reste acceptable. C'est seulement au moment où un vrai patron se dégage (≥2-3 domaines avec un besoin identique et stable) qu'on extrait vers `Shared`.

### Procédure d'extraction vers `Shared`

Quand une extraction est décidée :

1. Identifier la forme la plus générale et stable du concept (retirer tout ce qui est spécifique à un seul domaine d'origine).
2. Créer la classe dans `Domains/Shared/{ValueObjects|Contracts|Events}/`.
3. Remplacer les implémentations dupliquées dans chaque domaine par un appel à la version partagée.
4. Ajouter/mettre à jour les tests unitaires de la classe partagée dans `tests/Unit/Domains/Shared/`.
5. Vérifier qu'aucun domaine n'importe encore une classe métier d'un AUTRE domaine directement après l'extraction (l'extraction ne doit pas introduire de nouveau couplage domaine-à-domaine, seulement domaine-vers-Shared).

## 3. Code commun au-delà de l'application → package Composer interne

Si le code est réutilisable sur **plusieurs applications Laravel** de l'organisation (pas seulement plusieurs domaines d'une même app), ce n'est plus `Shared`, c'est un package versionné séparément :

```
votre-org/laravel-money
votre-org/laravel-api-kit
```

Distribué via un repository Composer privé (Satis, Private Packagist) ou en `path` repository pendant le développement local. Ça découple le cycle de release de ce code de celui de l'application, et impose un vrai contrat de compatibilité (semver) plutôt qu'une modification silencieuse partagée par tous.

**Signal qu'il est temps d'extraire en package** : le même code est copié-collé (ou envisagé pour l'être) dans un second projet Laravel de l'organisation, pas seulement un second domaine du même projet.

## Arbre de décision

```
Ce code est-il utilisé par un seul domaine ?
├── Oui → il reste dans ce domaine (Domains/{X}/...)
└── Non, par plusieurs domaines
    ├── Concept technique, sans sémantique métier ?
    │   └── Oui → app/Support/
    ├── Réutilisé au-delà de cette application (autre projet Laravel) ?
    │   └── Oui → package Composer interne
    └── Concept métier partagé, besoin CONSTATÉ (pas anticipé) par ≥2 domaines ?
        └── Oui → Domains/Shared/ (Contracts, Events, ValueObjects uniquement)
```

## Anti-patterns à corriger si rencontrés dans le code existant

- `Domains/Shared/` qui contient des Actions, des Repositories, ou des Models → chaque cas est un domaine métier déguisé en "commun" ; identifier le vrai domaine et déplacer.
- Une classe placée dans `Shared` dès sa création, sans qu'aucun second domaine ne l'utilise encore → la redescendre dans le domaine d'origine jusqu'à preuve du besoin réel.
- Un fichier nommé `Helper.php`, `Utils.php` ou `Common.php` n'importe où dans le projet → renommer selon une intention précise ou éclater en plusieurs classes nommées.
