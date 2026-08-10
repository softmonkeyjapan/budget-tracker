---
name: laravel-code-style
description: Conventions PHP transverses de cette app (strict_types, final, DTO, Pint). Utiliser pour toute nouvelle classe PHP créée dans app/, ou avant de considérer une tâche PHP terminée.
---

Règles appliquées à tout le code `app/` de cette app :

- `declare(strict_types=1);` en haut de **chaque** fichier PHP, juste après `<?php`.
- `final class` par défaut sur toute nouvelle classe (Controller, Action, Repository, FormRequest, Resource, Exception, Middleware, DTO). Exceptions à `final` : les classes conçues explicitement pour être étendues (`App\Http\Controllers\Controller`, `App\Support\Exceptions\DomainException`, classes abstraites) — dans ce cas ne pas mettre `final`, éventuellement `abstract`.
- DTO pour le transfert de données entre couches (Action ↔ Repository ↔ HTTP) : `readonly class`, un DTO par cas d'usage — voir skill `laravel-modular-architecture` → `04-dto-data.md` pour le détail (nommage, `fromRequest()`, ce qui n'est pas un DTO).
- Formatage : Laravel Pint, config par défaut (pas de `pint.json` custom). Lancer `./vendor/bin/pint` avant de considérer une tâche terminée — ne pas formatter à la main.
- Pas d'analyse statique (PHPStan/Larastan) dans ce projet — décision assumée, ne pas l'ajouter sans qu'on le redemande.
- Type-hint tout : paramètres, retours, propriétés. Pas de `mixed` sauf si vraiment polymorphe (ex. valeur brute avant cast).
