---
name: laravel-exceptions
description: Convention pour signaler une règle métier violée (pas une erreur de validation de formulaire) dans cette app Laravel. Utiliser dès qu'une Action doit refuser une opération pour une raison métier (état invalide, règle business, ressource déjà dans un état terminal, etc.).
---

Toute règle métier violée dans une Action lève une exception dédiée, jamais un `return false`/`return null` silencieux, jamais un `abort(422, '...')` inline.

Règles :
- Chaque exception métier étend `App\Support\Exceptions\DomainException` (`app/Support/Exceptions/DomainException.php`), qui fournit déjà un `render()` par défaut : redirection `back()->withErrors(['message' => ...])`, cohérent avec l'affichage d'erreurs Inertia standard (mêmes `errors` bag que la validation FormRequest).
- Nommage : `VerbeInfinitifImpossibleException` ou `EtatInvalideException` selon le cas (ex. `InsufficientStockException`, `AccountAlreadyVerifiedException`) — le nom doit se comprendre sans lire le message.
- Placée dans `app/Domains/{Domain}/Exceptions/` (pas dans `Support/` — seule la classe de base `DomainException` y vit, voir skill `laravel-modular-architecture` → `10-code-commun.md`).
- Levée depuis l'Action (jamais depuis le Controller ni le Repository — le Repository ne connaît pas les règles métier).
- Ne pas utiliser `DomainException`/ses filles pour de la validation de formulaire classique (format, champ requis, unicité) — ça reste le rôle du `FormRequest` + `ValidationException` standard de Laravel. La distinction : validation = la requête est mal formée ; exception métier = la requête est bien formée mais l'opération est refusée pour une raison business.
- Si un cas particulier a besoin d'un rendu différent du défaut (ex. statut HTTP spécifique, page dédiée), override `render()` dans la sous-classe plutôt que de modifier `DomainException`.
