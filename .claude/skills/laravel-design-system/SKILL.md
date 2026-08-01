---
name: laravel-design-system
description: Charte graphique et système de design de cette app (palette, typographie, composants Vue, écrans de référence). Consulter avant d'écrire ou modifier tout template/composant Vue, toute classe Tailwind, toute couleur ou tout espacement.
---

Source de vérité : `docs/design/sources/DESIGN-SYSTEM.md` — palette (clair/sombre), typographie (`Nunito Sans`), grille 8 px, rayons, ombres, et spec de chaque composant (`StatTile`, `BudgetProgress`, `CategoryCard`, `SuggestionRow`, `DataTable`, `MonthPicker`, `CategoryBudgetRow`). Le lire en entier avant de construire un écran — ne pas réinventer une couleur, un composant ou un espacement en dehors de ce qui y est défini.

Maquette statique de référence (HTML/CSS/JS autonome, prototype avant intégration Vue, pas du code applicatif à réutiliser tel quel) : `docs/design/sources/index.html` + `sources/app.js` + `sources/tailwind.css`, buildée dans `docs/design/build/`. Écrans accessibles par fragment d'URL : `#design-system`, `#dashboard`, `#general`, `#categories`, `#transactions`, `#budgets`, `#recurring`. Captures dans `docs/design/sources/references/`.

Règles :
- Toute nouvelle page/composant Vue (`resources/js/Pages/`, `resources/js/Components/`, cf. `laravel-inertia-vue`) doit reprendre les tokens et composants de `DESIGN-SYSTEM.md` — mêmes noms de composants, mêmes rôles de couleur (corail = budget/progression, bleu = actif/navigation, vert = revenu, rouge corail = dépense).
- Si un écran ou un cas non couvert par `DESIGN-SYSTEM.md` apparaît, l'ajouter au document (nouvelle section ou variante de composant) plutôt que d'improviser silencieusement dans le composant Vue — garder le design system comme unique source vraie, pas le code Vue.
- Le dossier `docs/design/` (sources + build) est un artefact de conception, indépendant du code applicatif Laravel/Inertia — ne pas le confondre avec `resources/js/`.
