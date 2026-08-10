---
name: laravel-modular-architecture
description: Utiliser cette skill pour concevoir, structurer, générer ou faire évoluer une application Laravel volumineuse (monolithe modulaire orienté domaine métier, API REST, gros trafic, plusieurs équipes). Déclencher cette skill dès que l'utilisateur mentionne "architecture Laravel", "gros projet Laravel", "refactoring de Services Laravel", "modules Laravel", "Domain-Driven Design Laravel", "Actions Laravel", "structure de dossiers Laravel", ou demande de créer un nouveau domaine/module, une Action, un DTO, un Repository, un Event, un ServiceProvider de domaine, ou un endpoint API respectant des conventions internes cohérentes. Couvre : découpage par domaines métier, Actions à responsabilité unique, DTOs en frontière de couche, Repositories avec interfaces, communication inter-domaines par Events, conventions API (Resources, Form Requests, versionning), tests, et conventions de nommage. Ne pas utiliser pour un simple CRUD Laravel jetable sans besoin de scalabilité ni de modularité.
---

# Architecture Laravel Modulaire (Domain-Driven, orientée API)

Cette skill encode une architecture pour applications Laravel **larges**, pensées API-first, avec plusieurs domaines métier qui doivent rester découplés dans le temps (jusqu'à extraction en microservice si besoin).

Elle remplace le pattern `Controller → Service → Repository` plat par un **monolithe modulaire** découpé par domaine, avec des règles strictes par type de fichier.

**Compatible API pure et monolithe Inertia/Vue.** Le cœur de l'architecture (Domains, Actions, DTOs, Repositories, Events) est identique dans les deux cas — seule la façade HTTP change (Controller qui retourne du JSON vs. un Controller qui retourne `Inertia::render()`). Si le projet sert des vues Inertia/Vue, lire `references/11-inertia-vuejs.md` en complément de `07-api-http.md`, pas à la place.

## Principe général

1. Le code est rangé **par domaine métier** (`app/Domains/{Domain}/...`), jamais par couche technique globale.
2. Chaque domaine est quasi-autonome : ses propres Actions, Models, DTOs, Events, Http, Policies, Repositories, routes.
3. Les domaines ne s'appellent jamais directement entre eux — ils communiquent par **Events** ou par des **interfaces partagées** dans `app/Domains/Shared`.
4. Pas de "Services" fourre-tout. Un cas d'usage = une classe **Action**.
5. Chaque frontière de couche (HTTP ↔ Action ↔ Persistence) passe par un **DTO** typé, jamais par un `array` brut ou un Eloquent Model exposé tel quel.

## Comment utiliser cette skill

Avant de générer ou modifier du code, identifie ce que l'utilisateur demande et lis le fichier de règles correspondant dans `references/` — ne charge que ce dont tu as besoin :

| Ce que demande l'utilisateur | Fichier à lire |
|---|---|
| Créer un nouveau domaine/module de zéro | `references/01-structure-dossiers.md` puis `references/02-domaines-modules.md` |
| Où ranger tel fichier / structure globale | `references/01-structure-dossiers.md` |
| Créer/refactorer un cas d'usage métier (ex: "passer une commande") | `references/03-actions.md` |
| Créer un objet de transfert de données / validation | `references/04-dto-data.md` |
| Accès données, requêtes complexes, interfaces | `references/05-repositories.md` |
| Faire communiquer deux domaines entre eux | `references/06-events-communication.md` |
| Endpoint API, Controller, Resource, versionning | `references/07-api-http.md` |
| Page/Controller Inertia+Vue, cohabitation avec une API, structure du front | `references/11-inertia-vuejs.md` (en plus de `07-api-http.md`) |
| Écrire des tests pour ce qui précède | `references/08-tests.md` |
| Nommage de classes/fichiers/routes | `references/09-conventions-nommage.md` |
| Code utilisé par plusieurs domaines, où le ranger | `references/10-code-commun.md` |

Si la demande touche plusieurs types de fichiers (ex: "ajoute un endpoint pour annuler une commande"), lis **tous** les fichiers de règles concernés avant de générer le code : typiquement `03-actions.md` + `04-dto-data.md` + `07-api-http.md` + `09-conventions-nommage.md`.

## Checklist rapide avant de livrer du code

- [ ] Le code est-il dans le bon `Domains/{Domain}`, pas dans `app/Http` ou `app/Models` globaux ?
- [ ] Y a-t-il une classe "Service" générique qui aurait dû être une Action ? → refactorer.
- [ ] Un Eloquent Model traverse-t-il une frontière HTTP sans passer par une Resource/DTO ? → corriger.
- [ ] Un domaine appelle-t-il directement une classe d'un autre domaine (hors `Shared`) ? → remplacer par un Event ou une interface partagée.
- [ ] Le nommage suit-il `references/09-conventions-nommage.md` ?
- [ ] Manque-t-il un test (`references/08-tests.md`) pour l'Action ou l'endpoint créé ?
- [ ] Une classe est-elle placée dans `Shared` sans besoin constaté par ≥2 domaines ? → voir `references/10-code-commun.md`, la redescendre dans son domaine d'origine.

## Ce que cette skill ne couvre pas

- Choix d'infra (queue driver, cache, hosting) — hors périmètre, à traiter au cas par cas.
- CQRS complet avec event sourcing — cette skill propose une séparation lecture/écriture légère seulement (voir `03-actions.md`), pas de CQRS complet sauf demande explicite.
- Microservices — cette architecture est un monolithe modulaire conçu pour *pouvoir* être scindé plus tard, pas une architecture microservices en soi.
- Framework front autre qu'Inertia/Vue (SPA découplée avec son propre repo, React/Next séparé) — `11-inertia-vuejs.md` couvre Inertia+Vue spécifiquement ; les principes de fond (Resources comme contrat, pas de Model brut exposé) restent transposables mais les exemples de code ne le sont pas tels quels.
