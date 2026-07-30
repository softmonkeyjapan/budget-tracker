---
name: laravel-testing-pest
description: Convention de tests Pest pour cette app Laravel (Feature + Unit). Utiliser à chaque nouvelle route/Controller (test Feature) ou nouvelle méthode de Service avec logique non triviale (test Unit).
---

Framework : Pest (pas PHPUnit brut — la syntaxe fonctionnelle `it()`/`test()` est la norme ici, cf. `tests/Feature/Auth/*.php` existants).

Deux niveaux, pas de choix arbitraire entre les deux :
- **Feature** (`tests/Feature/`) : pour tout endpoint HTTP. Utilise `RefreshDatabase`, une vraie base SQLite en mémoire (pas de mock), traverse Controller → Service → Repository → DB réellement. Assertions typiques : `$response->assertRedirect(...)`, `assertDatabaseHas(...)`, ou pour une page Inertia `assertInertia(fn (Assert $page) => $page->component('Dossier/Page'))`.
- **Unit** (`tests/Unit/`) : uniquement pour un Service dont une méthode contient de la logique métier non triviale (calcul, branchement conditionnel, orchestration de plusieurs repositories). Le `*RepositoryContract` est mocké (`Mockery::mock(XxxRepositoryContract::class)` ou `$this->mock()` de Pest), pas de DB. Ne pas écrire de test Unit pour un Service qui ne fait que déléguer un CRUD simple au Repository — ça duplique le test Feature sans rien apporter (le Feature suffit).

Règles communes :
- Un fichier de test par Controller (Feature) ou par Service (Unit), nommé `XxxTest.php`.
- `it('fait quelque chose de précis', function () { ... })` — le nom décrit le comportement observable, pas l'implémentation.
- Arrange/Act/Assert visible (pas besoin de commentaires pour les délimiter, juste des blocs clairs).
- Les exceptions métier (`App\Exceptions\DomainException` et filles) se testent avec `->throws(XxxException::class)` en Unit, ou en Feature via `assertSessionHasErrors()` (elles sont rendues comme un `redirect()->withErrors()`, voir skill `laravel-exceptions`).

Avant de considérer une tâche terminée : `php artisan test` doit passer en entier, pas seulement le nouveau test ajouté.
