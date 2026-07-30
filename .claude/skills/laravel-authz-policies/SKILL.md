---
name: laravel-authz-policies
description: Convention d'autorisation (Policies Laravel) pour cette app. Utiliser dès qu'une action doit être restreinte à certains utilisateurs (propriétaire d'une ressource, rôle, etc.).
---

L'autorisation vit exclusivement à la frontière HTTP, jamais dans le Service.

Règles :
- Une Policy par Model (`php artisan make:policy XxxPolicy --model=Xxx`), placée dans `app/Policies/`. Laravel la découvre automatiquement par convention de nom (`XxxPolicy` pour `Xxx`) — pas besoin de l'enregistrer manuellement sauf cas non conventionnel.
- Dans le Controller, appeler `$this->authorize('update', $model)` (ou `Gate::authorize`) **avant** d'appeler le Service. Si la vérification échoue, Laravel lève une 403 automatiquement — le Service n'est jamais atteint.
- Le Service ne reçoit donc que des appels déjà autorisés : pas de `if ($user->can(...))` à l'intérieur d'un Service. S'il te semble nécessaire d'y remettre une vérification, c'est probablement un signe que la Policy est incomplète, pas que le Service doit la dupliquer.
- Pour des règles globales non liées à un Model précis (ex. accès à une section admin), utiliser un `Gate::define` dans `AppServiceProvider::boot()` plutôt qu'une Policy.
- Dans les pages Vue, ne pas cacher un bouton/action uniquement côté front comme mesure de sécurité — c'est un plus UX, la vérité reste `$this->authorize()` côté serveur.
