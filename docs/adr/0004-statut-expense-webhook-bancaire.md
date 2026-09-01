---
status: accepted
---

# Statut sur Expense pour l'ingestion de notifications bancaires, pas un modèle séparé

## Contexte

On ajoute l'ingestion automatique de notifications bancaires (via un webhook statique alimenté par MacroDroid) pour créer des Dépenses sans saisie manuelle. Une notification parsée automatiquement peut avoir un montant incertain, aucune catégorie assignée, et parfois ne pas être reconnue du tout (format inattendu) — ce qui contredit la définition initiale de `Dépense` ("toujours arrivée, jamais un état 'peut-être'", catégorie obligatoire).

Deux options : garder `Expense` strictement au sens initial et créer un modèle séparé (ex: `PendingExpense`) pour les candidats non confirmés ; ou étendre `Expense` avec un statut (`brouillon` / `validée` / `rejetée`) et assouplir la contrainte de catégorie obligatoire pour les statuts non-validés.

## Décision

Extension de `Expense` avec une colonne `status` (`brouillon`, `validée`, `rejetée`) et `category_id`/`amount` rendus nullables. Une dépense `validée` respecte toujours la règle historique (catégorie enfant obligatoire, montant renseigné). Les dépenses `brouillon`/`rejetée` sont exclues des totaux et budgets partout où `Expense` est agrégé (repository filtré par défaut sur `validée`), et apparaissent uniquement dans un écran de triage dédié ("À traiter").

## Alternatives considérées

Modèle séparé (`PendingExpense` ou équivalent) avec promotion en `Expense` réelle à la validation. Écarté par choix explicite de l'utilisateur : plus simple à livrer et à maintenir pour un usage mono-utilisateur, au prix d'assouplir la définition de `Dépense` documentée dans `CONTEXT.md` (mis à jour en conséquence).

## Conséquences

- Toute requête qui liste ou agrège des `Expense` sans passer par le Repository (`ExpenseRepositoryInterface`) risque d'inclure des brouillons/rejetées par erreur — les méthodes de lecture du repository filtrent déjà sur `status = validée`, à respecter pour toute nouvelle requête.
- `category_id` et `amount` nullables sur toute la table, alors que ce n'est un état légitime que pour les statuts non-validés — pas de contrainte SQL empêchant une dépense validée d'avoir un montant `null` (comme pour la règle de catégorie enfant de l'ADR-0003, cette contrainte vit dans le code applicatif, pas en base).
- Si le besoin de tracer plusieurs sources de webhook apparaît (aujourd'hui un seul, BCI codé en dur), reconsidérer cette décision : un modèle séparé deviendrait plus justifié à ce moment-là.
