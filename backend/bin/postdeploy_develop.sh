#!/bin/bash

ROOT=$(cd $(dirname ${BASH_SOURCE[0]})/.. && pwd)

# Si INSTANCE_NUMBER n'est pas défini ou vaut 0, alors jouer la migration de base de données
if [ -z "${INSTANCE_NUMBER}" ] || [ ${INSTANCE_NUMBER} == 0 ]; then
  "${ROOT}/bin/console" doctrine:migration:migrate --no-interaction --all-or-nothing
fi

psql ${DATABASE_URL/\?*/} <<EOF
INSERT INTO agent_fournisseurs_identites (uid, nom, est_reseau_interne, est_actif, url_decouverte, domaines, administration) VALUES
('b95309cd-b87d-460d-be2d-f8086965290f', 'Extensso', false, true, 'https://auth.sso.fsi.interieur.rie.gouv.fr/.well-known/openid-configuration', '["gendarmerie.interieur.gouv.fr"]', 'GN'),
('cbbae5e2-44e5-4181-b42d-e387d83083d3', 'Passage2 (Réseau Interministériel de l''État)', false, true, 'https://auth.sso.interieur.rie.gouv.fr/.well-known/openid-configuration', '["interieur.gouv.fr"," allier.gouv.fr"," ain.gouv.fr"," aisne.gouv.fr"," alpes-de-haute-provence.gouv.fr"," aube.gouv.fr"," ardeche.gouv.fr"," alpes-maritimes.gouv.fr"," ardennes.gouv.fr"," ariege.gouv.fr"," aveyron.gouv.fr"," aude.gouv.fr"," bas-rhin.gouv.fr"," calvados.gouv.fr"," bouches-du-rhone.gouv.fr"," charente.gouv.fr"," cantal.gouv.fr"," charente-maritime.gouv.fr"," cher.gouv.fr"," correze.gouv.fr"," corse-du-sud.gouv.fr"," cote-dor.gouv.fr"," cotes-darmor.gouv.fr"," creuse.gouv.fr"," deux-sevres.gouv.fr"," dcstep.gouv.fr"," dordogne.gouv.fr"," doubs.gouv.fr"," essonne.gouv.fr"," drome.gouv.fr"," eure.gouv.fr"," eure-et-loir.gouv.fr"," gard.gouv.fr"," gers.gouv.fr"," gironde.gouv.fr"," grandest.gouv.fr"," guadeloupe.gouv.fr"," guyane.gouv.fr"," guyane.pref.gouv.fr"," haut-rhin.gouv.fr"," guadeloupe.pref.gouv.fr"," finistere.gouv.fr"," haute-corse.gouv.fr"," haute-garonne.gouv.fr"," haute-loire.gouv.fr"," haute-marne.gouv.fr"," haute-saone.gouv.fr"," haute-savoie.gouv.fr"," haute-vienne.gouv.fr"," hautes-alpes.gouv.fr"," hautes-pyrenees.gouv.fr"," herault.gouv.fr"," ille-et-vilaine.gouv.fr"," indre-et-loire.gouv.fr"," indre.gouv.fr"," jura.gouv.fr"," la-reunion.gouv.fr"," isere.gouv.fr"," loire-atlantique.gouv.fr"," loiret.gouv.fr"," loire.gouv.fr"," loir-et-cher.gouv.fr"," lot.gouv.fr"," lot-et-garonne.gouv.fr"," landes.gouv.fr"," lozere.gouv.fr"," maine-et-loire.gouv.fr"," manche.gouv.fr"," marne.gouv.fr"," martinique.gouv.fr"," martinique.pref.gouv.fr"," mayenne.gouv.fr"," mayotte.gouv.fr"," mayotte.pref.gouv.fr"," meurthe-et-moselle.gouv.fr"," meuse.gouv.fr"," moselle.gouv.fr"," nievre.gouv.fr"," nord.gouv.fr"," morbihan.gouv.fr"," oise.gouv.fr"," puy-de-dome.gouv.fr"," pyrenees-orientales.gouv.fr"," orne.gouv.fr"," pyrenees-atlantiques.gouv.fr"," pas-de-calais.gouv.fr"," reunion.gouv.fr"," reunion.pref.gouv.fr"," rhone.gouv.fr"," saone-et-loire.gouv.fr"," sarthe.gouv.fr"," savoie.gouv.fr"," seine-et-marne.gouv.fr"," seine-maritime.gouv.fr"," somme.gouv.fr"," tarn-et-garonne.gouv.fr"," tarn.gouv.fr"," territoire-de-belfort.gouv.fr"," val-doise.gouv.fr"," var.gouv.fr"," vaucluse.gouv.fr"," vendee.gouv.fr"," vienne.gouv.fr"," vosges.gouv.fr"," yonne.gouv.fr"," yvelines.gouv.fr"]', 'PN'),
('ee89db94-64de-4e14-b31a-e93ed3ab1168', 'Calypsso (Police Nationale)', false, true, 'https://auth.sso.police.interieur.gouv.fr/.well-known/openid-configuration', '["interieur.gouv.fr"]', 'PN'),
('e7782e47-8e0f-4b94-8e21-1197cb6e7143', 'Curasso (Gendarmerie Nationale)', false, true, 'https://auth.sso.gendarmerie.interieur.gouv.fr/.well-known/openid-configuration', '["gendarmerie.interieur.gouv.fr"," gendarmerie.defense.gouv.fr"]', 'GN'),
('bc064a76-0aab-4c89-af86-8f78a5e68aca', 'Ministère de la Justice', false, true, 'https://auth.agc.sso.justice.ader.gouv.fr/.well-known/openid-configuration', '["justice.fr"," justice.gouv.fr"," agrasc.gouv.fr"," externes.justice.gouv.fr"," externes.justice.fr"," riep-justice.fr"]', 'MJ'),
('5f6ce761-d16d-4db1-b72c-895e90152373', 'Cheops', false, true, 'https://auth.sso.fsi.interieur.rie.gouv.fr/.well-known/openid-configuration', '["interieur.gouv.fr"]', 'PN'),
('9e139e69-de07-4cbe-987f-d12cb38c0368', 'Ministère de la Justice', false, true, 'https://auth.agc.sso.justice.ader.gouv.fr/.well-known/openid-configuration', '["justice.fr"," agrasc.gouv.fr"," justice.gouv.fr"," externes.justice.fr"," riep-justice.fr"," externes.justice.gouv.fr"]', 'MJ')
ON CONFLICT (uid) DO NOTHING;
EOF


psql ${DATABASE_URL/\?*/} <<EOF
INSERT INTO agents (email, nom, prenom, roles, identifiant, uid, administration, donnes_authentification, est_valide, fournisseur_identite_uid, date_creation) VALUES
('policier@interieur.gouv.fr', 'Issié', 'Paul', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', 'e316bde4-3d37-4ed8-9001-c40d9b07fe06', '1337', 'PN', '', true, 'ee89db94-64de-4e14-b31a-e93ed3ab1168', now() - interval '3 weeks'),
('gendarme@gendarmerie.interieur.gouv.fr', 'd''Harme', 'Jean', 'ROLE_AGENT,ROLE_AGENT_FORCES_DE_L_ORDRE', 'c7f94b68-89c9-4df4-ac44-1d127937dd63', '1337', 'GN', '', true, 'e7782e47-8e0f-4b94-8e21-1197cb6e7143', now() - interval '2 weeks')
ON CONFLICT (email) DO NOTHING;
EOF