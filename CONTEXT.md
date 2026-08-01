# Budget Tracker

Application de suivi budgétaire personnel remplaçant un tableur Excel mensuel par une app centralisée.

## Language

**Catégorie** (`Category`):
Regroupement de Dépenses défini par l'utilisateur, organisé en hiérarchie auto-référencée : une catégorie racine (ex: Alimentaire) et ses catégories enfants (ex: Boucherie, Alimentation générale). Le modèle autorise une profondeur illimitée, mais l'usage actuel se limite à 2 niveaux — une catégorie qui a déjà une catégorie parente ne peut pas elle-même avoir d'enfants, et seule une catégorie enfant peut recevoir des Dépenses. Dédiée aux dépenses uniquement (les revenus utilisent Entrée d'argent, non catégorisée). Porte une couleur et une icône ; si absentes sur une catégorie enfant, elles héritent de celles de sa catégorie parente.
_Avoid_: Grande catégorie / Sous-catégorie (ce sont des rôles selon la position dans la hiérarchie, pas des types distincts), Type de dépense, Poste budgétaire

**Dépense** (`Expense`):
Mouvement d'argent réel sortant, toujours arrivé (jamais un état "peut-être"), rattaché obligatoirement à une catégorie enfant. Porte un montant, une date précise, et une description libre optionnelle.
_Avoid_: Transaction (terme abandonné — désignait avant cette itération aussi bien les dépenses que les revenus), Achat

**Entrée d'argent** (`Income`):
Mouvement d'argent réel entrant (salaire, freelance, etc.), indépendant de toute Catégorie. Porte un montant, une date précise, et une description libre optionnelle. Plusieurs Entrées d'argent peuvent coexister sur un même mois (ex: salaire + revenu freelance).
_Avoid_: Revenu catégorisé, Transaction de type revenu

**Mois**:
Regroupement virtuel des Dépenses et Entrées d'argent d'une période, dérivé de leur date (année + mois) — pas une entité à part entière. Base de tous les calculs du tableau de bord et des comparaisons entre mois. Aucune restriction de saisie rétroactive ; pas de notion de mois "ouvert" ou "clôturé".
_Avoid_: Période budgétaire, Mois clôturé

**Pourcentage d'utilisation**:
Pour une Catégorie donnée sur un Mois donné, rapport entre la somme de ses Dépenses et la somme des Entrées d'argent du même Mois. Base du camembert du tableau de bord, où 100% représente les Entrées d'argent du mois.
_Avoid_: Objectif atteint, Budget consommé
