# Budget Tracker v2 — Design system

## Direction visuelle

Cockpit financier personnel, chaleureux et calme. La palette, la typographie, la grille, les rayons et les ombres restent identiques à la version précédente.

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
| Texte discret | `#8A90A2` |
| Bleu navigation | `#2F80ED` |
| Vert entrée/confirmation | `#23C48E` |
| Rouge dépense | `#FF5B62` |
| Violet catégorie | `#8A5CF6` |

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

- `Nunito Sans`, puis `Poppins`, puis `system-ui`.
- Titres : 22–32 px, graisse 800.
- Corps : 14 px / 22 px.
- Grille : 8 px.
- Coque : rayon 24 px ; cartes : 16 px ; contrôles : 12 px.
- Ombres diffuses, aucune bordure dure.

## Composants

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
