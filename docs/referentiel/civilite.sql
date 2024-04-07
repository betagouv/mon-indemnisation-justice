INSERT INTO civilite(id, code, libelle, mnemo)VALUES(nextval('civilite_id_seq'), '1', 'Monsieur','M') ON CONFLICT DO NOTHING;
INSERT INTO civilite(id, code, libelle, mnemo)VALUES(nextval('civilite_id_seq'), '2', 'Madame','MME') ON CONFLICT DO NOTHING;
