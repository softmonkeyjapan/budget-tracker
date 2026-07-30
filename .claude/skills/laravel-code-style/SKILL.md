---
name: laravel-code-style
description: Conventions PHP transverses de cette app (strict_types, final, Pint). Utiliser pour toute nouvelle classe PHP créée dans app/, ou avant de considérer une tâche PHP terminée.
---

Règles appliquées à tout le code `app/` de cette app (déjà en place sur l'auth Breeze refactorée — s'en inspirer) :

- `declare(strict_types=1);` en haut de **chaque** fichier PHP, juste après `<?php`.
- `final class` par défaut sur toute nouvelle classe (Controller, Service, Repository, FormRequest, Resource, Exception, Middleware). Exceptions à `final` : les classes conçues explicitement pour être étendues (`App\Http\Controllers\Controller`, classes abstraites) — dans ce cas ne pas mettre `final`, éventuellement `abstract`.
- Pas de DTO custom ni de package externe pour le transfert de données entre couches — un `array` typé via la phpdoc (`@param array{name: string, email: string} $data` ou `array<string, mixed>`) suffit, validé en amont par un `FormRequest`. Décision assumée pour rester simple ; ne pas réintroduire de DTO sans que ça soit redemandé explicitement.
- Formatage : Laravel Pint, config par défaut (pas de `pint.json` custom). Lancer `./vendor/bin/pint` avant de considérer une tâche terminée — ne pas formatter à la main.
- Pas d'analyse statique (PHPStan/Larastan) dans ce projet — décision assumée, ne pas l'ajouter sans qu'on le redemande.
- Type-hint tout : paramètres, retours, propriétés. Pas de `mixed` sauf si vraiment polymorphe (ex. valeur brute avant cast).
