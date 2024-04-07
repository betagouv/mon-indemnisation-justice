INSERT INTO categorie(id, code, libelle, mnemo)VALUES(nextval('categorie_id_seq'), '1', 'Bris de glace','BRI') ON CONFLICT DO NOTHING;
