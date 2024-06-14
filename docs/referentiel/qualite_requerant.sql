INSERT INTO qualite_requerant(id, code, libelle, mnemo)VALUES(nextval('qualite_requerant_id_seq'), '1', 'Propriétaire','PRO') ON CONFLICT DO NOTHING;
INSERT INTO qualite_requerant(id, code, libelle, mnemo)VALUES(nextval('qualite_requerant_id_seq'), '2', 'Locataire','LOC') ON CONFLICT DO NOTHING;
INSERT INTO qualite_requerant(id, code, libelle, mnemo)VALUES(nextval('qualite_requerant_id_seq'), '3', 'Hébergeant','HEB') ON CONFLICT DO NOTHING;
INSERT INTO qualite_requerant(id, code, libelle, mnemo)VALUES(nextval('qualite_requerant_id_seq'), '4', 'Autre','AUT') ON CONFLICT DO NOTHING;
