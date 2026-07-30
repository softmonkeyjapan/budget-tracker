---
name: laravel-exceptions
description: Convention pour signaler une règle métier violée (pas une erreur de validation de formulaire) dans cette app Laravel. Utiliser dès qu'un Service doit refuser une opération pour une raison métier (état invalide, règle business, ressource déjà dans un état terminal, etc.).
---

Toute règle métier violée dans un Service lève une exception dédiée, jamais un `return false`/`return null` silencieux, jamais un `abort(422, '...')` inline.

Règles :
- Chaque exception métier étend `App\Exceptions\DomainException` (`app/Exceptions/DomainException.php`), qui fournit déjà un `render()` par défaut : redirection `back()->withErrors(['message' => ...])`, cohérent avec l'affichage d'erreurs Inertia standard (mêmes `errors` bag que la validation FormRequest).
- Nommage : `VerbeInfinitifImpossibleException` ou `EtatInvalideException` selon le cas (ex. `InsufficientStockException`, `AccountAlreadyVerifiedException`) — le nom doit se comprendre sans lire le message.
- Placée dans `app/Exceptions/`.
- Levée depuis le Service (jamais depuis le Controller ni le Repository — le Repository ne connaît pas les règles métier, voir skill `laravel-repository-pattern`).
- Ne pas utiliser `App\Exceptions\DomainException`/ses filles pour de la validation de formulaire classique (format, champ requis, unicité) — ça reste le rôle du `FormRequest` + `ValidationException` standard de Laravel. La distinction : validation = la requête est mal formée ; exception métier = la requête est bien formée mais l'opération est refusée pour une raison business.
- Si un cas particulier a besoin d'un rendu différent du défaut (ex. statut HTTP spécifique, page dédiée), override `render()` dans la sous-classe plutôt que de modifier `DomainException`.
