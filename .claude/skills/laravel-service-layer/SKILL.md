---
name: laravel-service-layer
description: Convention pour écrire une classe Service (app/Services) dans cette app Laravel. Utiliser dès qu'un Controller a besoin d'exécuter de la logique métier (créer/modifier/supprimer une entité, orchestrer plusieurs Repositories, appliquer une règle métier).
---

Un Service regroupe la logique métier d'un domaine (une entité ou un agrégat), pas une seule action. Exemple de référence : `app/Services/UserService.php`.

Règles :
- `final class XxxService`, `declare(strict_types=1)`.
- Une méthode par cas d'usage métier, nommée à l'impératif (`register`, `updateProfile`, `deleteAccount`) — pas de méthodes génériques `handle()`/`execute()`.
- Injection du/des `*RepositoryContract` (interface, jamais l'implémentation concrète) via le constructeur en `private readonly`.
- Le Service ne connaît ni HTTP (`Request`, `redirect()`), ni Inertia. Il prend des types PHP natifs ou des arrays validés en paramètre, retourne des Models ou `void`.
- Les données entrantes sont des `array` déjà validés par un `FormRequest` (pas de DTO dans ce projet — décision prise volontairement pour rester simple).
- Une règle métier violée (pas une erreur de validation de formulaire) lève une exception dédiée qui étend `App\Exceptions\DomainException` — voir skill `laravel-exceptions`. Ne jamais retourner `null`/`false` silencieusement pour signaler un échec métier.
- Le Service ne fait pas d'autorisation (`$this->authorize()`) — c'est la responsabilité du Controller via une Policy, voir skill `laravel-authz-policies`.
- Pas de logique de persistence brute (`Model::where(...)`) dans le Service : ça vit dans le Repository, voir skill `laravel-repository-pattern`.

Où le brancher : le Controller injecte le Service (constructeur) et appelle une seule méthode par action HTTP. Le Controller reste fin — s'il fait plus que valider (FormRequest), appeler le Service, et retourner une réponse, la logique en trop appartient au Service.
