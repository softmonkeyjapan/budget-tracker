---
name: laravel-inertia-vue
description: Convention Controller/props/pages pour le stack Inertia + Vue de cette app. Utiliser pour tout nouvel endpoint qui rend une page, toute nouvelle page Vue, ou tout changement aux props partagées.
---

Stack : Inertia + Vue 3 (pas de TypeScript, pas de SSR — décisions prises au setup). Pas d'API JSON séparée : les Controllers rendent directement des pages Inertia.

Controller :
- `Inertia::render('Dossier/NomDeLaPage', [...])` — le nom de la page correspond à un fichier dans `resources/js/Pages/Dossier/NomDeLaPage.vue`.
- Les props ne sont jamais un Model Eloquent brut. Toujours une `JsonResource` (`App\Http\Resources\...`) ou un array construit à la main. Raison : évite de fuiter des colonnes sensibles (`password`, `remember_token`) et découple la forme des props du schéma DB. Exemple : `UserResource` utilisée dans `app/Http/Middleware/HandleInertiaRequests.php`.
- Le Controller ne fait que : valider (FormRequest) → appeler le Service → `Inertia::render`/`redirect()`. Toute logique de préparation de données non triviale (agrégation, filtrage complexe) va dans le Service, pas dans le Controller.
- Props partagées globalement (user connecté, flash messages) : `app/Http/Middleware/HandleInertiaRequests.php::share()`.

Vue :
- Une page = un composant sous `resources/js/Pages/`, PascalCase, qui correspond exactement au premier argument de `Inertia::render`.
- Composants réutilisables sous `resources/js/Components/`, Layouts sous `resources/js/Layouts/`.
- Les formulaires utilisent `useForm` d'Inertia (`@inertiajs/vue3`) pour poster vers les routes — pas de `fetch`/`axios` manuel pour les actions CRUD classiques.
- Navigation interne via le helper Ziggy `route('nom.route')`, jamais d'URL en dur.
- Pas de logique métier dans les `.vue` (calculs de règles, validations dupliquées) — le Vue affiche et déclenche des requêtes, la validation/logique reste côté serveur (FormRequest/Service). Le seul rôle du JS est l'UX (affichage des erreurs renvoyées par Inertia, états de chargement).
