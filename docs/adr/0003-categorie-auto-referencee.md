---
status: accepted
---

# Catégorie en table unique auto-référencée, pas deux tables Grande Catégorie / Sous-catégorie

## Contexte

La saisie d'une Dépense se fait en deux temps (grande catégorie puis sous-catégorie, ex: Alimentaire → Boucherie). La façon la plus directe de modéliser ça serait deux tables distinctes (`grande_categories`, `categories`) reflétant les deux rôles vus dans l'UI. L'utilisateur préfère un schéma plus souple, réutilisable si une hiérarchie plus profonde devenait utile plus tard.

## Décision

Une seule table `categories`, avec une colonne `parent_id` nullable pointant vers `categories.id`. Une catégorie sans parent est une racine (rôle "grande catégorie" dans l'UI actuelle) ; une catégorie avec parent est un enfant (rôle "sous-catégorie"). Le schéma autorise une profondeur illimitée, mais l'usage est contraint à 2 niveaux au niveau service/validation, pas en contrainte SQL : une catégorie qui a déjà un parent ne peut pas elle-même devenir parente, et une Dépense ne peut s'attacher qu'à une catégorie qui a un parent.

## Alternatives considérées

Deux tables distinctes (`grande_categories` + `categories` avec FK obligatoire vers une grande catégorie). Écarté au profit de la table unique auto-référencée : l'utilisateur veut pouvoir approfondir la hiérarchie plus tard sans migration de schéma, seulement en assouplissant la règle de profondeur au niveau service.

## Conséquences

- Les requêtes remontant la hiérarchie (couleur héritée, libellé complet) passent par une jointure sur elle-même plutôt qu'une simple FK vers une table dédiée.
- La contrainte "2 niveaux max, Dépense jamais sur une racine" vit dans le code applicatif, pas dans le schéma — une régression applicative pourrait théoriquement laisser passer une hiérarchie plus profonde sans que la base ne s'y oppose.
- Assouplir la profondeur plus tard (si le besoin apparaît) ne demande aucune migration, juste un changement de règle de validation.
