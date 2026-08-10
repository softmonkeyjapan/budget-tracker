---
name: laravel-inertia-vue
description: Convention Controller/props/pages pour le stack Inertia + Vue de cette app. Utiliser pour tout nouvel endpoint qui rend une page, toute nouvelle page Vue, ou tout changement aux props partagées.
---

Stack : Inertia + Vue 3 (pas de TypeScript, pas de SSR — décisions prises au setup et maintenues malgré les exemples `.ts` de `laravel-modular-architecture`). Pas d'API JSON séparée aujourd'hui : les Controllers rendent directement des pages Inertia. Ne pas créer de façade `Http/Controllers/Api/` par anticipation — voir skill `laravel-modular-architecture` → `11-inertia-vuejs.md` (règle "duplication d'abord").

Controller :
- `Inertia::render('Dossier/NomDeLaPage', [...])` — le nom de la page correspond à un fichier dans `resources/js/Domains/{Domain}/Pages/Dossier/NomDeLaPage.vue`.
- Les props ne sont **jamais** un Model Eloquent brut ni un array construit à la main — toujours une `JsonResource` (`Domains/{Domain}/Http/Resources/...`). Règle resserrée par rapport à avant : plus d'array ad hoc toléré, voir `laravel-modular-architecture` → `07-api-http.md` ("API Resources obligatoires, sans exception").
- Le Controller ne fait que : valider (FormRequest) → construire un DTO → appeler l'Action → `Inertia::render`/`redirect()`. Toute logique de préparation de données non triviale (agrégation, filtrage complexe) va dans l'Action, pas dans le Controller.
- `store`/`update`/`destroy` retournent des redirections (`to_route()`), pas du JSON — Inertia gère le rechargement côté front via `router.post()`/`useForm()`.
- Props partagées globalement (user connecté, flash messages) : `app/Http/Middleware/HandleInertiaRequests.php::share()`.

Vue :
- Front en miroir des domaines back : `resources/js/Domains/{Domain}/Pages/`, `.../Components/` (composants propres au domaine), `.../Composables/`. Composants génériques sans sémantique métier sous `resources/js/Shared/Components` (ex-`resources/js/Components/ui`), Layouts sous `resources/js/Shared/Layouts`.
- Une page = un composant PascalCase qui correspond exactement au premier argument de `Inertia::render`. Adapter le resolver `app.ts`/`app.js` en conséquence (voir `laravel-modular-architecture` → `11-inertia-vuejs.md`).
- Les formulaires utilisent `useForm` d'Inertia (`@inertiajs/vue3`) pour poster vers les routes — pas de `fetch`/`axios` manuel pour les actions CRUD classiques.
- Navigation interne via le helper Ziggy `route('nom.route')`, jamais d'URL en dur.
- Pas de logique métier dans les `.vue` (calculs de règles, validations dupliquées) — le Vue affiche et déclenche des requêtes, la validation/logique reste côté serveur (FormRequest/Action). Le seul rôle du JS est l'UX (affichage des erreurs renvoyées par Inertia, états de chargement).
