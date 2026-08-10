---
name: laravel-authz-policies
description: Convention d'autorisation (Policies Laravel) pour cette app. Utiliser dès qu'une action doit être restreinte à certains utilisateurs (propriétaire d'une ressource, rôle, etc.).
---

L'autorisation vit exclusivement à la frontière HTTP, jamais dans l'Action.

Règles :
- Une Policy par Model, placée dans `app/Domains/{Domain}/Policies/` (pas `app/Policies/` global — voir skill `laravel-modular-architecture` → `01-structure-dossiers.md`). Laravel ne la découvre plus automatiquement une fois sortie de `app/Policies` : l'enregistrer explicitement dans `{Domain}ServiceProvider::boot()` via `Gate::policy(Xxx::class, XxxPolicy::class)`.
- Dans le Controller, appeler `$this->authorize('update', $model)` (ou `Gate::authorize`) **avant** d'appeler l'Action. Si la vérification échoue, Laravel lève une 403 automatiquement — l'Action n'est jamais atteinte.
- L'Action ne reçoit donc que des appels déjà autorisés : pas de `if ($user->can(...))` à l'intérieur d'une Action. S'il te semble nécessaire d'y remettre une vérification, c'est probablement un signe que la Policy est incomplète, pas que l'Action doit la dupliquer.
- Pour des règles globales non liées à un Model précis (ex. accès à une section admin), utiliser un `Gate::define` dans `AppServiceProvider::boot()` plutôt qu'une Policy.
- Dans les pages Vue, ne pas cacher un bouton/action uniquement côté front comme mesure de sécurité — c'est un plus UX, la vérité reste `$this->authorize()` côté serveur.
