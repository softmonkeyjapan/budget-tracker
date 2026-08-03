# Budget Tracker v2 — build statique

## Prévisualisation

Ouvrir directement `build/index.html`. Il est totalement autonome : CSS et JavaScript sont intégrés dans le fichier pour fonctionner avec les restrictions de sécurité des URL `file://`.

## Contenu

```text
build/
  index.html      # build autonome à ouvrir
  app.js          # copie séparée pour inspection
  styles.css      # copie séparée pour inspection
sources/
  index.html
  app.js
  tailwind.css
  v2.css
  DESIGN-SYSTEM.md
```

`build/styles.css` est le résultat compilé. `sources/tailwind.css` contient la source Tailwind v4 et `sources/v2.css` les extensions métier de cette refonte.

Le bouton lune/soleil bascule entre modes clair et sombre. Le thème système est utilisé par défaut ; le choix est mémorisé si le navigateur autorise `localStorage` sur les fichiers locaux.

## Navigation

- `#design-system`
- `#dashboard`
- `#general`
- `#categories`
- `#expense`
- `#income`
