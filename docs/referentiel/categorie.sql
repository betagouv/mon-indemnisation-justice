INSERT INTO categorie(id, code, libelle, mnemo)VALUES(nextval('categorie_id_seq'), '1', 'Bris de porte','BRI') ON CONFLICT DO NOTHING;
