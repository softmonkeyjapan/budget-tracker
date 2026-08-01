---
status: superseded by ADR-0002
---

# Budget séparé des Transactions (objectif mensuel par Catégorie, pas de champ "prévu" sur la Transaction)

## Contexte

Ce projet remplace un tableur Excel personnel où chaque ligne de dépense/revenu porte deux colonnes, **Planifié** et **Actuel**, saisies manuellement sur la même ligne (ex: "Loyer" planifié 120,000 FCFP / actuel 40,000 FCFP ; "Anniversaire Louis" planifié 16,000 FCFP / actuel 0 FCFP car pas encore fêté). Le tableur est recréé chaque mois à partir d'un modèle vierge — c'est précisément cette corvée mensuelle que l'application doit éliminer.

En modélisant le domaine, on a d'abord validé un modèle qui calque directement l'Excel : une seule entité `Transaction` portant deux montants optionnels, `montant_prevu` et `montant_reel`, cohabitant sur la même ligne. Ce modèle a été remis en question volontairement pour explorer des alternatives plus proches des pratiques établies dans les applications de budgétisation grand public (YNAB, Firefly III, Actual Budget), qui séparent strictement la notion de *ce qui est prévu* de *ce qui s'est réellement passé*.

## Décision

On sépare deux entités distinctes :

- **`Transaction`** : représente uniquement un mouvement d'argent **réel**, déjà survenu. Porte un montant (obligatoire), une date, une Catégorie, et une description libre. Il n'existe aucun état "transaction pas encore arrivée" — si rien ne s'est passé, il n'y a pas de Transaction.
- **`Budget`** : un objectif **mensuel**, une seule valeur numérique, fixé sur une Catégorie pour un mois donné. Comparé, au niveau des tableaux de bord, à la somme des Transactions réelles de cette Catégorie sur ce mois. Chaque Catégorie porte une option "reconduction automatique" : si activée, le dernier Budget fixé reste la cible des mois suivants jusqu'à modification explicite ; si désactivée, il n'y a pas de Budget pour un mois tant qu'il n'a pas été fixé à la main pour ce mois précis.

Le rattrapage du besoin "je veux prévoir une charge fixe sans la ressaisir chaque mois" (ex: Loyer, Netflix, Spotify) est couvert par une entité séparée, `Transaction Récurrente` : un modèle (description, Catégorie, montant) qui **suggère** chaque mois une Transaction à confirmer, sans jamais en créer une automatiquement et silencieusement. Modifier ou supprimer une Transaction Récurrente n'affecte jamais les Transactions déjà confirmées des mois passés.

## Alternatives considérées

1. **Prévu + Réel sur la même ligne de Transaction** (calque direct de l'Excel). Rejeté : sémantiquement bancal (une "Transaction" avec seulement un montant prévu n'a rien d'une transaction — rien n'a eu lieu), et complexifie chaque lecture/rapport qui doit gérer trois états possibles par ligne (prévu seul, réel seul, les deux).
2. **Entre-deux : Transaction toujours réelle + entité "Prévision" libre non liée formellement** (pense-bête indépendant, sans contrainte de schéma). Écarté au profit de l'option retenue une fois que l'utilisateur a confirmé préférer l'approche catégorie/mois, plus proche des standards du marché et suffisante pour son usage (il ne cherche pas à reproduire l'Excel à l'identique, juste à en garder la simplicité d'usage).
3. **Budget par Catégorie/mois, Transactions 100% réelles** (retenue). Écarté un temps par prudence — perd la granularité "je prévois X pour cette dépense précise" (ex: anniversaire d'un enfant) que l'Excel permettait ligne par ligne — mais l'utilisateur a tranché en faveur de la simplicité et de la proximité avec les outils établis, quitte à perdre cette granularité fine.

## Conséquences

- Ajouter une dépense/un revenu est une action unique et sans ambiguïté : un montant, une date, une catégorie. Pas de formulaire à deux montants, pas de distinction "brouillon/confirmé" à gérer dans l'UI.
- Les tableaux de bord "Planifié vs Actuel" se calculent par agrégation (`SUM(Transaction.montant) WHERE catégorie = X AND mois = Y`) comparée à `Budget.montant_cible`, sans jointure ligne à ligne.
- On perd la capacité de noter un montant anticipé pour une dépense ponctuelle précise et nommée (ex: "Anniversaire Louis: ~16,000 prévu") — seul un objectif agrégé par catégorie et par mois existe. Si ce besoin se manifeste concrètement à l'usage, l'option 2 (Prévision libre non liée) reste la voie d'extension naturelle, sans remise en cause du modèle Transaction/Budget actuel.
- Le mécanisme "suggestion à confirmer" des Transactions Récurrentes implique un état intermédiaire (suggestions en attente, potentiellement plusieurs mois de retard si ignorées) qui n'existait pas dans l'Excel — à couvrir dans le plan d'implémentation (ex: où et comment ces suggestions s'affichent, ce qui se passe si elles s'accumulent).
