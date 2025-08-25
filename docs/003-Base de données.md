# Base de données

## Schéma actuel

```mermaid
---
title: Mon Indemisation Justice
---
classDiagram
direction BT
class adresse {
   varchar(255) ligne1
   varchar(255) ligne2
   varchar(255) code_postal
   varchar(255) localite
   varchar(5) commune_code
   integer id
}
class agent_fournisseurs_identites {
   varchar(255) nom
   boolean est_reseau_interne
   boolean est_actif
   varchar(255) url_decouverte
   json domaines
   varchar(255) administration
   varchar(255) uid
}
class agents {
   varchar(180) email
   varchar(50) nom
   varchar(30) prenom
   roles  /* (DC2Type:simple_array) */ text
   varchar(255) identifiant
   varchar(255) uid
   varchar(255) administration
   text donnes_authentification
   boolean est_valide
   varchar(255) fournisseur_identite_uid
   timestamp(0) date_creation
   integer id
}
class bris_porte {
   integer adresse_id
   varchar(255) numero_pv
   date date_operation_pj
   boolean is_porte_blindee
   varchar(255) precision_requerant
   integer requerant_id
   varchar(20) reference
   double precision proposition_indemnisation
   varchar(20) raccourci
   varchar(3) qualite_requerant
   timestamp(0) date_creation
   integer test_eligibilite_id
   integer etat_actuel_id
   integer redacteur_id
   text notes
   varchar(2) type_institution_securite_publique
   boolean est_lie_attestation
   integer id
}
class doctrine_migration_versions {
   integer execution_time
   varchar(191) version
   timestamp(0) executed_at
}
class document {
   integer id
   varchar(255) filename
   varchar(40) type
   varchar(255) size
   varchar(255) original_filename
   varchar(255) mime
   integer validateur_id
   date_ajout  /* (DC2Type:datetime_immutable) */ timestamp(0)
   boolean est_ajout_requerant
   boolean est_valide
   date_validation  /* (DC2Type:datetime_immutable) */ timestamp(0)
   text corps
   json meta_donnees
}
class document_dossiers {
   integer dossier_id
   integer document_id
}
class dossier_courriers {
   integer dossier_id
   integer agent_id
   integer requerant_id
   varchar(255) filename
   date_creation  /* (DC2Type:datetime_immutable) */ timestamp(0)
   integer id
}
class dossier_etats {
   integer dossier_id
   integer agent_id
   integer requerant_id
   varchar(255) etat
   date  /* (DC2Type:datetime_immutable) */ timestamp(0)
   json contexte
   integer id
}
class eligibilite_tests {
   integer id
   varchar(3) departement_code
   text description
   boolean est_vise
   boolean est_hebergeant
   boolean est_proprietaire
   boolean a_contacte_assurance
   boolean a_contacte_bailleur
   integer requerant_id
   boolean est_eligible_experimentation
   timestamp(0) date_soumission
   boolean est_issu_attestation
}
class geo_codes_postaux {
   varchar(5) code_commune
   varchar(5) code_postal
   timestamp(0) date_derniere_maj
   boolean est_actif
   integer id
}
class geo_communes {
   varchar(3) departement_code
   varchar(255) nom
   timestamp(0) date_derniere_maj
   boolean est_actif
   varchar(5) code
}
class geo_departements {
   varchar(2) region_code
   varchar(255) nom
   boolean est_deploye
   timestamp(0) date_derniere_maj
   boolean est_actif
   varchar(3) code
}
class geo_pays {
   varchar(255) nom
   varchar(5) code_insee
   timestamp(0) date_derniere_maj
   boolean est_actif
   varchar(3) code
}
class geo_regions {
   varchar(32) nom
   varchar(2) code
}
class institutions_securite_publique {
   date date_integration
   varchar(2) type
}
class personne_morale {
   varchar(255) siren_siret
   varchar(255) raison_sociale
   integer id
}
class personne_physique {
   varchar(13) numero_securite_sociale
   varchar(255) nom
   varchar(255) prenom1
   varchar(255) prenom2
   varchar(255) prenom3
   varchar(255) telephone
   varchar(3) pays_naissance
   date date_naissance
   varchar(255) nom_naissance
   varchar(255) email
   varchar(3) civilite
   integer code_postal_naissance_id
   integer id
}
class requerants {
   integer id
   integer adresse_id
   integer personne_physique_id
   integer personne_morale_id
   varchar(180) email
   roles  /* (DC2Type:simple_array) */ text
   varchar(255) password
   boolean est_verifie_courriel
   boolean is_personne_morale
   varchar(12) jeton_verification
   varchar(255) sub
   timestamp(0) date_inscription
}
class sessions {
   bytea sess_data
   integer sess_lifetime
   integer sess_time
   varchar(128) sess_id
}

agents  -->  agent_fournisseurs_identites : fournisseur_identite_uid => uid
bris_porte  -->  adresse : adresse_id => id
bris_porte  -->  agents : redacteur_id => id
bris_porte  -->  dossier_etats : etat_actuel_id => id
bris_porte  -->  eligibilite_tests : test_eligibilite_id => id
bris_porte  -->  institutions_securite_publique : type_institution_securite_publique => type
bris_porte  -->  requerants : requerant_id => id
document  -->  agents : validateur_id => id
document_dossiers  -->  bris_porte : dossier_id => id
document_dossiers  -->  document : document_id => id
dossier_courriers  -->  agents : agent_id => id
dossier_courriers  -->  bris_porte : dossier_id => id
dossier_courriers  -->  requerants : requerant_id => id
dossier_etats  -->  agents : agent_id => id
dossier_etats  -->  bris_porte : dossier_id => id
dossier_etats  -->  requerants : requerant_id => id
eligibilite_tests  -->  geo_departements : departement_code => code
eligibilite_tests  -->  requerants : requerant_id => id
geo_codes_postaux  -->  geo_communes : code_commune => code
geo_communes  -->  geo_departements : departement_code => code
geo_departements  -->  geo_regions : region_code => code
personne_physique  -->  geo_codes_postaux : code_postal_naissance_id => id
personne_physique  -->  geo_pays : pays_naissance => code
personne_physique  -->  geo_pays : code_postal_naissance_id => code
requerants  -->  adresse : adresse_id => id
requerants  -->  personne_morale : personne_morale_id => id
requerants  -->  personne_physique : personne_physique_id => id
```