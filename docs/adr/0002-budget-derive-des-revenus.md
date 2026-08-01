---
status: accepted
---

# Budget dérivé des revenus, pas d'objectif fixé par Catégorie (supersède ADR-0001)

## Contexte

ADR-0001 avait posé un `Budget` : un objectif mensuel fixé à la main sur une Catégorie, indépendant des revenus réels, comparé aux Dépenses réelles pour produire un pourcentage d'utilisation. En reprenant le projet pour une version simplifiée, ce modèle s'est avéré plus complexe que nécessaire pour l'usage visé : l'utilisateur veut simplement voir, mois par mois, quelle part de ses revenus part dans chaque catégorie — pas fixer et ajuster un objectif par catégorie.

## Décision

On supprime l'entité `Budget`. Le pourcentage d'utilisation d'une Catégorie sur un Mois se calcule directement : `SUM(Dépenses de la catégorie) / SUM(Entrées d'argent du mois)`. Le camembert du tableau de bord représente 100% des Entrées d'argent du mois, chaque part étant les Dépenses réelles d'une catégorie — pas un objectif alloué à l'avance.

## Alternatives considérées

Garder `Budget` (ADR-0001) en plus des Entrées d'argent, pour permettre un objectif par catégorie distinct des revenus réels. Écarté par choix de simplicité assumé par l'utilisateur : la granularité d'un objectif par catégorie n'est pas un besoin exprimé pour cette itération.

## Conséquences

- Perte de la capacité à fixer un objectif par catégorie indépendant des revenus réels (ex: "je veux limiter Loisirs à 50 000 FCFP même si mes revenus montent") — seul le ratio dépense/revenu global existe.
- Perte de la "reconduction automatique" d'objectif d'un mois sur l'autre (n'a plus de sens sans `Budget`).
- Si le besoin d'objectifs par catégorie revient, ADR-0001 reste la voie d'extension naturelle sans remise en cause du modèle Catégorie/Dépense/Entrée d'argent actuel.
