<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203082259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la base de données (version compactée du 3 février 2026)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse (id SERIAL NOT NULL, commune_code VARCHAR(5) DEFAULT NULL, ligne1 VARCHAR(255) DEFAULT NULL, ligne2 VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, localite VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C35F0816E5127261 ON adresse (commune_code)');
        $this->addSql('CREATE TABLE agent_fournisseurs_identites (uid VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, est_reseau_interne BOOLEAN NOT NULL, est_actif BOOLEAN NOT NULL, url_decouverte VARCHAR(255) NOT NULL, domaines JSON NOT NULL, administration VARCHAR(5) DEFAULT NULL, PRIMARY KEY(uid))');
        $this->addSql('CREATE TABLE agents (id SERIAL NOT NULL, fournisseur_identite_uid VARCHAR(255) DEFAULT NULL, identifiant VARCHAR(255) NOT NULL, uid VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(16) DEFAULT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(30) NOT NULL, administration VARCHAR(255) DEFAULT NULL, donnes_authentification TEXT DEFAULT NULL, est_valide BOOLEAN DEFAULT false NOT NULL, roles TEXT NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9596AB6E2CCC2389 ON agents (fournisseur_identite_uid)');
        $this->addSql('CREATE UNIQUE INDEX uniq_agent_identifiant ON agents (identifiant)');
        $this->addSql('CREATE UNIQUE INDEX uniq_agent_email ON agents (email)');
        $this->addSql('COMMENT ON COLUMN agents.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE bris_porte (id SERIAL NOT NULL, requerant_id INT NOT NULL, redacteur_id INT DEFAULT NULL, etat_actuel_id INT DEFAULT NULL, test_eligibilite_id INT DEFAULT NULL, declaration_id UUID DEFAULT NULL, adresse_id INT DEFAULT NULL, notes TEXT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_depot TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, numero_pv VARCHAR(255) DEFAULT NULL, qualite_requerant VARCHAR(3) DEFAULT NULL, description_requerant TEXT DEFAULT NULL, type_institution_securite_publique VARCHAR(2) DEFAULT NULL, type_attestation VARCHAR(20) DEFAULT NULL, reference VARCHAR(20) DEFAULT NULL, proposition_indemnisation DOUBLE PRECISION DEFAULT NULL, date_operation_pj DATE DEFAULT NULL, is_porte_blindee BOOLEAN DEFAULT false NOT NULL, precision_requerant VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BC580EED4A93DAA5 ON bris_porte (requerant_id)');
        $this->addSql('CREATE INDEX IDX_BC580EED764D0490 ON bris_porte (redacteur_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDF90DA413 ON bris_porte (etat_actuel_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EED9450CE1E ON bris_porte (test_eligibilite_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDC06258A3 ON bris_porte (declaration_id)');
        $this->addSql('CREATE INDEX IDX_BC580EED4DE7DC5C ON bris_porte (adresse_id)');
        $this->addSql('COMMENT ON COLUMN bris_porte.declaration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE document_dossiers (dossier_id INT NOT NULL, document_id INT NOT NULL, PRIMARY KEY(dossier_id, document_id))');
        $this->addSql('CREATE INDEX IDX_2C4FD3BD611C0C56 ON document_dossiers (dossier_id)');
        $this->addSql('CREATE INDEX IDX_2C4FD3BDC33F7837 ON document_dossiers (document_id)');
        $this->addSql('CREATE TABLE brouillons_declaration_fdo_bris_porte (id UUID NOT NULL, agent_id INT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donnees JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C88D0A73414710B ON brouillons_declaration_fdo_bris_porte (agent_id)');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE coordonnees_requerant (id SERIAL NOT NULL, civilite VARCHAR(3) DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, courriel VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE declarations_fdo_bris_porte (id UUID NOT NULL, adresse_id INT NOT NULL, procedure_id UUID NOT NULL, coordonnees_requerant_id INT DEFAULT NULL, dossier_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, reference VARCHAR(6) NOT NULL, est_erreur VARCHAR(6) NOT NULL, description_erreur TEXT DEFAULT NULL, date_operation DATE NOT NULL, precisions_requerant TEXT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_soumission TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_suppression TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_74EC5CF74DE7DC5C ON declarations_fdo_bris_porte (adresse_id)');
        $this->addSql('CREATE INDEX IDX_74EC5CF71624BCD2 ON declarations_fdo_bris_porte (procedure_id)');
        $this->addSql('CREATE INDEX IDX_74EC5CF73E76DA05 ON declarations_fdo_bris_porte (coordonnees_requerant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74EC5CF7611C0C56 ON declarations_fdo_bris_porte (dossier_id)');
        $this->addSql('CREATE INDEX declarations_fdo_bris_porte_agent_idx ON declarations_fdo_bris_porte (agent_id)');
        $this->addSql('CREATE UNIQUE INDEX declarations_fdo_bris_porte_reference_idx ON declarations_fdo_bris_porte (reference)');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.procedure_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.date_operation IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.date_soumission IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.date_suppression IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE declaration_fdo_bris_porte_pieces_jointes (declaration_id UUID NOT NULL, document INT NOT NULL, PRIMARY KEY(declaration_id, document))');
        $this->addSql('CREATE INDEX IDX_7FF00AAAC06258A3 ON declaration_fdo_bris_porte_pieces_jointes (declaration_id)');
        $this->addSql('CREATE INDEX IDX_7FF00AAAD8698A76 ON declaration_fdo_bris_porte_pieces_jointes (document)');
        $this->addSql('COMMENT ON COLUMN declaration_fdo_bris_porte_pieces_jointes.declaration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE document (id SERIAL NOT NULL, validateur_id INT DEFAULT NULL, est_valide BOOLEAN DEFAULT NULL, date_validation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, mime VARCHAR(255) DEFAULT NULL, date_ajout TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_ajout_requerant BOOLEAN DEFAULT NULL, corps TEXT DEFAULT NULL, meta_donnees JSON DEFAULT NULL, date_derniere_modification TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, type VARCHAR(40) NOT NULL, size INT DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A76E57AEF2F ON document (validateur_id)');
        $this->addSql('COMMENT ON COLUMN document.date_validation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN document.date_ajout IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN document.date_derniere_modification IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE dossier_etats (id SERIAL NOT NULL, dossier_id INT NOT NULL, agent_id INT DEFAULT NULL, requerant_id INT DEFAULT NULL, etat VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, contexte JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_71671FCF611C0C56 ON dossier_etats (dossier_id)');
        $this->addSql('CREATE INDEX IDX_71671FCF3414710B ON dossier_etats (agent_id)');
        $this->addSql('CREATE INDEX IDX_71671FCF4A93DAA5 ON dossier_etats (requerant_id)');
        $this->addSql('COMMENT ON COLUMN dossier_etats.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE eligibilite_tests (id SERIAL NOT NULL, departement_code VARCHAR(3) DEFAULT NULL, requerant_id INT DEFAULT NULL, est_vise BOOLEAN DEFAULT NULL, est_hebergeant BOOLEAN DEFAULT NULL, rapport_au_logement VARCHAR(3) DEFAULT NULL, a_contacte_assurance BOOLEAN DEFAULT NULL, a_contacte_bailleur BOOLEAN DEFAULT NULL, est_eligible_experimentation BOOLEAN NOT NULL, est_issu_attestation BOOLEAN DEFAULT true NOT NULL, date_soumission TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_881877C16A333750 ON eligibilite_tests (departement_code)');
        $this->addSql('CREATE INDEX IDX_881877C14A93DAA5 ON eligibilite_tests (requerant_id)');
        $this->addSql('CREATE TABLE geo_codes_postaux (id SERIAL NOT NULL, code_commune VARCHAR(5) NOT NULL, code_postal VARCHAR(5) NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFDB17BBDA459572 ON geo_codes_postaux (code_commune)');
        $this->addSql('CREATE INDEX idx_code_postal ON geo_codes_postaux (code_postal)');
        $this->addSql('CREATE UNIQUE INDEX unique_code_insee_postal ON geo_codes_postaux (code_postal, code_commune)');
        $this->addSql('CREATE TABLE geo_communes (code VARCHAR(5) NOT NULL, departement_code VARCHAR(3) DEFAULT NULL, nom VARCHAR(255) NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX IDX_B2E795046A333750 ON geo_communes (departement_code)');
        $this->addSql('CREATE TABLE geo_departements (code VARCHAR(3) NOT NULL, region_code VARCHAR(2) DEFAULT NULL, nom VARCHAR(255) NOT NULL, est_deploye BOOLEAN DEFAULT false NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX IDX_19A85389AEB327AF ON geo_departements (region_code)');
        $this->addSql('CREATE TABLE geo_pays (code VARCHAR(3) NOT NULL, nom VARCHAR(255) NOT NULL, code_insee VARCHAR(5) DEFAULT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX idx_pays_code_insee ON geo_pays (code_insee)');
        $this->addSql('CREATE TABLE geo_regions (code VARCHAR(2) NOT NULL, nom VARCHAR(32) NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE TABLE institutions_securite_publique (type VARCHAR(2) NOT NULL, date_integration DATE DEFAULT NULL, PRIMARY KEY(type))');
        $this->addSql('CREATE TABLE personne_morale (id SERIAL NOT NULL, siren_siret VARCHAR(255) DEFAULT NULL, raison_sociale VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE personne_physique (id SERIAL NOT NULL, code_postal_naissance_id INT DEFAULT NULL, pays_naissance VARCHAR(3) DEFAULT NULL, civilite VARCHAR(3) DEFAULT NULL, numero_securite_sociale VARCHAR(13) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, prenom1 VARCHAR(255) DEFAULT NULL, prenom2 VARCHAR(255) DEFAULT NULL, prenom3 VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, nom_naissance VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C2B29A2EF5A5513 ON personne_physique (code_postal_naissance_id)');
        $this->addSql('CREATE INDEX IDX_5C2B29A2695C56A1 ON personne_physique (pays_naissance)');
        $this->addSql('CREATE TABLE procedures_judiciaires (id UUID NOT NULL, numero_procedure VARCHAR(255) NOT NULL, service_enqueteur VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, juridiction_ou_parquet VARCHAR(255) DEFAULT NULL, nom_magistrat VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN procedures_judiciaires.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE requerants (id SERIAL NOT NULL, adresse_id INT DEFAULT NULL, personne_physique_id INT DEFAULT NULL, personne_morale_id INT DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, sub VARCHAR(255) DEFAULT NULL, est_verifie_courriel BOOLEAN NOT NULL, email VARCHAR(180) NOT NULL, roles TEXT NOT NULL, date_inscription TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, jeton_verification VARCHAR(12) DEFAULT NULL, is_personne_morale BOOLEAN DEFAULT false NOT NULL, navigation JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C44DE7DC5C ON requerants (adresse_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C454472AC9 ON requerants (personne_physique_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C435FE3BF6 ON requerants (personne_morale_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON requerants (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_SUB ON requerants (sub)');
        $this->addSql('COMMENT ON COLUMN requerants.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE _cache_limitateur_connexion (item_id VARCHAR(255) NOT NULL, item_data BYTEA NOT NULL, item_lifetime INT DEFAULT NULL, item_time INT NOT NULL, PRIMARY KEY(item_id))');
        $this->addSql('CREATE TABLE sessions (sess_id VARCHAR(128) NOT NULL, sess_data BYTEA NOT NULL, sess_lifetime INT NOT NULL, sess_time INT NOT NULL, PRIMARY KEY(sess_id))');
        $this->addSql('CREATE INDEX sess_lifetime_idx ON sessions (sess_lifetime)');
        $this->addSql('ALTER TABLE adresse ADD CONSTRAINT FK_C35F0816E5127261 FOREIGN KEY (commune_code) REFERENCES geo_communes (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE agents ADD CONSTRAINT FK_9596AB6E2CCC2389 FOREIGN KEY (fournisseur_identite_uid) REFERENCES agent_fournisseurs_identites (uid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED764D0490 FOREIGN KEY (redacteur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDF90DA413 FOREIGN KEY (etat_actuel_id) REFERENCES dossier_etats (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED9450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_dossiers ADD CONSTRAINT FK_2C4FD3BD611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_dossiers ADD CONSTRAINT FK_2C4FD3BDC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE brouillons_declaration_fdo_bris_porte ADD CONSTRAINT FK_5C88D0A73414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF74DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF71624BCD2 FOREIGN KEY (procedure_id) REFERENCES procedures_judiciaires (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73E76DA05 FOREIGN KEY (coordonnees_requerant_id) REFERENCES coordonnees_requerant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF7611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes ADD CONSTRAINT FK_7FF00AAAC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes ADD CONSTRAINT FK_7FF00AAAD8698A76 FOREIGN KEY (document) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76E57AEF2F FOREIGN KEY (validateur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C16A333750 FOREIGN KEY (departement_code) REFERENCES geo_departements (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C14A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE geo_codes_postaux ADD CONSTRAINT FK_CFDB17BBDA459572 FOREIGN KEY (code_commune) REFERENCES geo_communes (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE geo_communes ADD CONSTRAINT FK_B2E795046A333750 FOREIGN KEY (departement_code) REFERENCES geo_departements (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE geo_departements ADD CONSTRAINT FK_19A85389AEB327AF FOREIGN KEY (region_code) REFERENCES geo_regions (code) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2EF5A5513 FOREIGN KEY (code_postal_naissance_id) REFERENCES geo_codes_postaux (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2695C56A1 FOREIGN KEY (pays_naissance) REFERENCES geo_pays (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C44DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C454472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C435FE3BF6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse DROP CONSTRAINT FK_C35F0816E5127261');
        $this->addSql('ALTER TABLE agents DROP CONSTRAINT FK_9596AB6E2CCC2389');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4A93DAA5');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED764D0490');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDF90DA413');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDC06258A3');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4DE7DC5C');
        $this->addSql('ALTER TABLE document_dossiers DROP CONSTRAINT FK_2C4FD3BD611C0C56');
        $this->addSql('ALTER TABLE document_dossiers DROP CONSTRAINT FK_2C4FD3BDC33F7837');
        $this->addSql('ALTER TABLE brouillons_declaration_fdo_bris_porte DROP CONSTRAINT FK_5C88D0A73414710B');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF74DE7DC5C');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF71624BCD2');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73E76DA05');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF7611C0C56');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73414710B');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes DROP CONSTRAINT FK_7FF00AAAC06258A3');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes DROP CONSTRAINT FK_7FF00AAAD8698A76');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76E57AEF2F');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF611C0C56');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF3414710B');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF4A93DAA5');
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C16A333750');
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C14A93DAA5');
        $this->addSql('ALTER TABLE geo_codes_postaux DROP CONSTRAINT FK_CFDB17BBDA459572');
        $this->addSql('ALTER TABLE geo_communes DROP CONSTRAINT FK_B2E795046A333750');
        $this->addSql('ALTER TABLE geo_departements DROP CONSTRAINT FK_19A85389AEB327AF');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2EF5A5513');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2695C56A1');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C44DE7DC5C');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C454472AC9');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C435FE3BF6');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE agent_fournisseurs_identites');
        $this->addSql('DROP TABLE agents');
        $this->addSql('DROP TABLE bris_porte');
        $this->addSql('DROP TABLE document_dossiers');
        $this->addSql('DROP TABLE brouillons_declaration_fdo_bris_porte');
        $this->addSql('DROP TABLE coordonnees_requerant');
        $this->addSql('DROP TABLE declarations_fdo_bris_porte');
        $this->addSql('DROP TABLE declaration_fdo_bris_porte_pieces_jointes');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE dossier_etats');
        $this->addSql('DROP TABLE eligibilite_tests');
        $this->addSql('DROP TABLE geo_codes_postaux');
        $this->addSql('DROP TABLE geo_communes');
        $this->addSql('DROP TABLE geo_departements');
        $this->addSql('DROP TABLE geo_pays');
        $this->addSql('DROP TABLE geo_regions');
        $this->addSql('DROP TABLE institutions_securite_publique');
        $this->addSql('DROP TABLE personne_morale');
        $this->addSql('DROP TABLE personne_physique');
        $this->addSql('DROP TABLE procedures_judiciaires');
        $this->addSql('DROP TABLE requerants');
        $this->addSql('DROP TABLE _cache_limitateur_connexion');
        $this->addSql('DROP TABLE sessions');
    }
}
