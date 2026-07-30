# Contacts

Ce contexte gère le carnet de contacts personnel de chaque utilisateur connecté.

## Language

**Contact**:
Une personne enregistrée par un utilisateur, appartenant exclusivement à cet utilisateur (owner). Identifié par un prénom et/ou un nom. Peut avoir zéro ou plusieurs emails et zéro ou plusieurs numéros de téléphone.
_Avoid_: Personne, entrée, fiche

**Contact Email / Contact Phone**:
Une coordonnée (email ou téléphone) rattachée à un Contact. Chacune porte un Label et peut être marquée Principale.
_Avoid_: Coordonnée, adresse

**Label**:
Catégorie fermée attachée à un Contact Email ou Contact Phone. Valeurs: Personnel, Travail, Autre.
_Avoid_: Type, catégorie, tag

**Principal (Primary)**:
Marqueur indiquant l'email ou le téléphone à utiliser par défaut pour un Contact. Au plus un email principal et un téléphone principal par Contact.
_Avoid_: Par défaut, favori
