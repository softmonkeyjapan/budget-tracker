# Budget Tracker v2 — Design system

## Direction visuelle

Cockpit financier personnel, chaleureux et calme. Depuis l'adoption complète du thème shadcn-vue (lot 4), les composants d'interface (boutons, dialogues, menus, avatar) s'appuient sur les primitives shadcn/reka-ui ; l'identité (palette, typographie, ombres diffuses) reste la même, mais les rayons ont été harmonisés à l'échelle standard shadcn.

## Modèle mental

- Le revenu réel du mois est la base `100 %`.
- Les dépenses occupent des parts de cette base.
- Le solde est `entrées − dépenses`.
- Aucun budget ou objectif n’est saisi manuellement.
- Une dépense cible obligatoirement une catégorie enfant.
- Une entrée d’argent n’a aucune catégorie.

## Palette

| Rôle | Valeur |
|---|---:|
| Fond pêche | `#FFD2C4` |
| Accent corail | `#FF8A66` |
| Surface application | `#F3F5FA` |
| Surface carte | `#FFFFFF` |
| Texte principal | `#172033` |
| Texte discret | `#676E80` |
| Bleu navigation | `#2F80ED` |
| Vert entrée/confirmation | `#23C48E` |
| Rouge dépense | `#FF5B62` |
| Violet catégorie | `#8A5CF6` |

Texte discret retouché de `#8A90A2` à `#676E80` : le premier ne tenait que 3.2:1 sur fond blanc et 3:1 sur fond app, sous le seuil WCAG AA (4.5:1) pour du texte 14 px. `#676E80` tient 5.1:1 sur blanc et 4.7:1 sur `--color-app` (`#F3F5FA`, le fond le plus fréquent). Mode sombre non touché : `#AAB0C0` tenait déjà ~7:1 sur les fonds sombres, large marge AA.

### Mode sombre

| Rôle | Valeur |
|---|---:|
| Fond extérieur | `#241A1D` |
| Surface application | `#171B25` |
| Surface carte | `#202633` |
| Surface secondaire | `#282F3E` |
| Texte principal | `#F6F3F4` |
| Texte discret | `#AAB0C0` |
| Séparateur | `#343C4D` |
| Bleu actif | `#62A4FF` |
| Vert positif | `#46D6A4` |
| Rouge dépense | `#FF747A` |
| Corail | `#FF9A7A` |

Le thème suit le système lors de la première ouverture, puis mémorise le choix utilisateur lorsque le navigateur autorise le stockage local.

## Typographie et géométrie

- Corps : `Nunito Sans`, puis `Poppins`, puis `system-ui`.
- Titres (`font-heading`) : `Poppins`, puis `Nunito Sans`, puis `system-ui` — les deux familles étaient déjà chargées mais la distinction titres/corps n'était pas exploitée.
- Titres : 22–32 px, graisse 800.
- Corps : 14 px / 22 px.
- Grille : 8 px.
- Rayons harmonisés à l'échelle shadcn (`--radius: 0.75rem`) : contrôles `rounded-lg` (12 px), cartes `rounded-xl` (16 px), coque `rounded-3xl` (24 px, classe Tailwind standard — plus de tokens `--radius-shell/card/control` custom).
- Ombres diffuses, aucune bordure dure.

## Composants

Primitives shadcn-vue en place : `Button`, `Checkbox`, `Input`, `Label`, `Dialog` (derrière `Modal.vue`), `DropdownMenu` (menu utilisateur de la sidebar, pattern `NavUser` : avatar + nom + chevron, ouvre sur le côté en desktop / en bas en mobile), `Avatar`, `Sheet`, `Separator`, `Skeleton`, `Tooltip`, `Toggle`, `Sidebar`, `Chart` (donut `IncomeDistributionPie` du Dashboard, `SubcategoryBarChart`, et les deux graphiques de l'écran Comparaison — revenus/dépenses par mois en barres groupées, solde mensuel en barres signées — tous rendus via Unovis avec une couleur par catégorie/série issue de la base plutôt que la palette `--chart-1..5` par défaut, non utilisée ici).

Sidebar : bascules confidentialité/thème au-dessus du menu utilisateur dans le footer (pas l'inverse).

Tooltips de chart : bulle sombre fixe (`#172033`, texte blanc) quel que soit le thème — un fond réactif au thème casserait la lisibilité en dark mode. `VisTooltip` ne se câble pas correctement sur `VisSingleContainer` (le Donut ne reçoit jamais ses triggers) ; le survol du donut est géré à la main via l'attribut `__data__` posé par D3 sur chaque segment.

`Modal.vue` hérite du chrome par défaut de `DialogContent` shadcn : bouton de fermeture (X) en haut à droite (masqué si `closeable=false`) et overlay `bg-black/80`, remplaçant l'ancien overlay `bg-gray-500/75` custom.

### `IncomeDistributionPie`

Camembert où `100 % = somme des entrées d’argent du mois`. Chaque part représente les dépenses réelles d’une catégorie racine. La portion non dépensée reste gris clair. Le centre affiche le total des revenus.

### `CategoryUsageRow`

Icône, catégorie racine ou enfant, montant dépensé et pourcentage `dépenses catégorie / entrées du mois`. Ne jamais afficher d’objectif.

### `CategoryTree`

Deux niveaux maximum. Racine visuellement forte ; enfants indentés. Un badge enfant identique à la racine signifie un héritage. Une couleur différente indique une surcharge locale. Racines et enfants triés par ordre alphabétique. Accordéon exclusif sur les racines : toutes repliées par défaut, ouvrir une racine referme celle qui était ouverte, un chevron indique l'état.

### `ExpenseForm`

Sélection en deux étapes : racine, puis enfant. L’enfant est obligatoire. Champs suivants : montant entier, date libre, description facultative.

### `IncomeForm`

Montant entier, date libre, description facultative. Aucun contrôle de catégorie.

### `MonthPicker`

Mois précédent, libellé `MMMM YYYY`, mois suivant. La date du formulaire peut cibler librement un mois passé.

### `FeedbackBubble`

Bulle flottante ancrée en bas à droite, visible sur tous les écrans authentifiés (jamais sur les écrans invités). Ouvre le `Modal` partagé avec un unique champ texte libre. Couleur `bg-nav` (bleu, cohérent avec les autres actions primaires de l'app), icône bulle de dialogue sobre. Confirmation de succès en vert (`text-income`), erreurs via le composant `InputError` existant.

## Écrans

1. Dashboard mensuel : revenus, dépenses, solde, camembert, utilisation par catégorie, cinq dernières dépenses.
2. Comparaison : revenus, dépenses et solde réels mois par mois.
3. Catégories : gestion racines/enfants, icône, couleur, héritage.
4. Saisie dépense : racine → enfant, montant, date, description.
5. Entrée d’argent : montant, date, description.

## Retiré

- Budget manuel et objectifs.
- `BudgetProgress`.
- `CategoryBudgetRow`.
- Écran Budget mensuel.
- Transactions récurrentes et suggestions.
