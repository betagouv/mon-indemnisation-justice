# Base de données

## Le requérant et son dossier

Un **usager** peut créer son compte sur la plateforme et ainsi déposer un **dossier** de demande d'indemnisation. La
table `usagers` contient ainsi principalement des informations en relation avec la plateforme (mot de passe, jeton pour
la vérification de l'adresse courriel, etc...).

Un usager est également une **personne** dont la tables `personnes` recense les informations de base de l'état civil.
Lorsqu'il dépose un dossier à son nom, l'usager lie son dossier à sa **personne physique**, la table `personnes_physiques`
étend donc les données de personnes avec les informations de naissance (date, pays et ville ou commune, si né•e en France.

Un dossier, de la table`dossiers`, possède une référence, un état actuel (voir après ["États d'un dossier"](#"États d'un dossier"))
et est présentement _forcément_ de type `BRIS_PORTE` et donc associé à une entrée de la table `bris_porte` qui contient
les informations spécifiques aux préjudices de portes brisées (date de l'opération de police judiciaire, adresse, porte
blindée ou non, etc...).

Puisque chaque dossier est déposé par un usager au nom d'une personne physique ou d'une personne morale, on peut rapidement
savoir si la `personnes` associée à la personne physique ou si le représentant légal de la personne morale est l'usager
ou s'il s'agit d'une délégation un d'un mandat.  

```mermaid
---
title: "Mon Indemisation Justice : le requérant et son dossier"
---
classDiagram
direction BT

class adresse {
   integer id
   varchar(255) ligne1
   varchar(255) ligne2
   varchar(255) code_postal
   varchar(255) localite
   varchar(5) commune_code
   
}
class bris_porte {
   integer id
   integer test_eligibilite_id
   declaration_id
   integer adresse_id
   varchar(16) rapport_au_logement
   varchar(255) precision_rapport_au_logement
   text description_requerant
   varchar(2) type_institution_securite_publique
   varchar(20) type_attestation
   date date_operation
   boolean est_porte_blindee
   
}
class dossiers {
   integer id
   integer usager_id
   varchar(20) reference
   double precision proposition_indemnisation
   timestamp(0) date_creation
   integer etat_actuel_id
   integer redacteur_id
   text notes
   timestamp(0) date_depot
   integer requerant_personne_physique_id
   integer requerant_personne_morale_id
   bris_porte_id
   varchar(64) type
}
class personnes {
   uuid id
   varchar(3) civilite
   varchar(255) prenom
   varchar(255) nom
   varchar(255) nom_naissance
   varchar(255) courriel
   varchar(255) telephone
   
}
class personnes_morales {
   integer id
   varchar(255) siren_siret
   varchar(255) raison_sociale
   representant_legal_id
}
class personnes_physiques {
   integer id
   varchar(13) numero_securite_sociale
   varchar(255) prenom2
   varchar(255) prenom3
   varchar(3) pays_naissance
   date date_naissance
   integer code_postal_naissance_id
   personne_id
   integer adresse_id
}
class usagers {
   integer id
   varchar(180) email
   roles text
   varchar(255) password
   boolean est_verifie_courriel
   boolean is_personne_morale
   varchar(12) jeton_verification
   varchar(255) sub
   timestamp(0) date_inscription
   json navigation
   personne_id
}

bris_porte  -->  adresse : adresse_id => id
dossiers  -->  bris_porte : bris_porte_id => id
dossiers  -->  personnes_morales : requerant_personne_morale_id => id
dossiers  -->  personnes_physiques : requerant_personne_physique_id => id
dossiers  -->  usagers : usager_id => id
personnes_morales  -->  personnes : representant_legal_id => id
personnes_physiques  -->  adresse : adresse_id => id
personnes_physiques  -->  personnes : personne_id => id
usagers  -->  personnes : personne_id => id

```

## États d'un dossier

TODO : à compléter