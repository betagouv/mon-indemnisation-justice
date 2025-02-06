<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812155048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial import';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE adresse_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE categorie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE civilite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE liasse_documentaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE personne_morale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE personne_physique_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prejudice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE qualite_requerant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_enqueteur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE statut_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tracking_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE adresse (id INT NOT NULL, ligne1 VARCHAR(255) DEFAULT NULL, ligne2 VARCHAR(255) DEFAULT NULL, ligne3 VARCHAR(255) DEFAULT NULL, lieu_dit VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, localite VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE bris_porte (id INT NOT NULL, adresse_id INT DEFAULT NULL, qualite_requerant_id INT DEFAULT NULL, receveur_attestation_id INT NOT NULL, service_enqueteur_id INT NOT NULL, numero_pv VARCHAR(255) DEFAULT NULL, date_operation_pj DATE DEFAULT NULL, is_porte_blindee BOOLEAN DEFAULT false, is_erreur_porte BOOLEAN DEFAULT false, identite_personne_recherchee VARCHAR(255) DEFAULT NULL, nom_remise_attestation VARCHAR(255) DEFAULT NULL, prenom_remise_attestation VARCHAR(255) DEFAULT NULL, precision_requerant VARCHAR(255) DEFAULT NULL, date_attestation_information DATE DEFAULT NULL, numero_parquet VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BC580EED4DE7DC5C ON bris_porte (adresse_id)');
        $this->addSql('CREATE INDEX IDX_BC580EED7288B5CD ON bris_porte (qualite_requerant_id)');
        $this->addSql('CREATE INDEX IDX_BC580EEDD7035A02 ON bris_porte (receveur_attestation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EED484A214 ON bris_porte (service_enqueteur_id)');
        $this->addSql('CREATE TABLE categorie (id INT NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_497DD63477153098 ON categorie (code)');
        $this->addSql('CREATE TABLE civilite (id INT NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C4C1BD677153098 ON civilite (code)');
        $this->addSql('CREATE TABLE document (id INT NOT NULL, liasse_documentaire_id INT NOT NULL, filename VARCHAR(255) DEFAULT NULL, type VARCHAR(40) NOT NULL, size VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A76BCC8DD14 ON document (liasse_documentaire_id)');
        $this->addSql('CREATE INDEX document_liasse_documentaire_id_type_idx ON document (liasse_documentaire_id, type)');
        $this->addSql('CREATE TABLE liasse_documentaire (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE personne_morale (id INT NOT NULL, liasse_documentaire_id INT NOT NULL, siren_siret VARCHAR(255) DEFAULT NULL, raison_sociale VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_56031D2ABCC8DD14 ON personne_morale (liasse_documentaire_id)');
        $this->addSql('CREATE TABLE personne_physique (id INT NOT NULL, civilite_id INT DEFAULT NULL, qualite_id INT DEFAULT NULL, liasse_documentaire_id INT NOT NULL, numero_securite_sociale VARCHAR(13) DEFAULT NULL, code_securite_sociale VARCHAR(2) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, prenom1 VARCHAR(255) DEFAULT NULL, prenom2 VARCHAR(255) DEFAULT NULL, prenom3 VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, portable VARCHAR(255) DEFAULT NULL, commune_naissance VARCHAR(255) DEFAULT NULL, pays_naissance VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, nom_naissance VARCHAR(255) DEFAULT NULL, precision VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C2B29A239194ABF ON personne_physique (civilite_id)');
        $this->addSql('CREATE INDEX IDX_5C2B29A2A6338570 ON personne_physique (qualite_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C2B29A2BCC8DD14 ON personne_physique (liasse_documentaire_id)');
        $this->addSql('CREATE TABLE prejudice (id INT NOT NULL, requerant_id INT NOT NULL, liasse_documentaire_id INT NOT NULL, date_declaration DATE DEFAULT NULL, reference VARCHAR(20) DEFAULT NULL, note TEXT DEFAULT NULL, proposition_indemnisation NUMERIC(10, 2) DEFAULT NULL, motivation_proposition TEXT DEFAULT NULL, raccourci VARCHAR(20) DEFAULT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_39465C1F4A93DAA5 ON prejudice (requerant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_39465C1FBCC8DD14 ON prejudice (liasse_documentaire_id)');
        $this->addSql('CREATE TABLE qualite_requerant (id INT NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37D0575F77153098 ON qualite_requerant (code)');
        $this->addSql('CREATE TABLE service_enqueteur (id INT NOT NULL, nom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, courriel VARCHAR(255) DEFAULT NULL, numero_pv VARCHAR(255) DEFAULT NULL, juridiction VARCHAR(255) DEFAULT NULL, magistrat VARCHAR(255) DEFAULT NULL, numero_parquet VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE statut (id INT NOT NULL, prejudice_id INT NOT NULL, emetteur_id INT NOT NULL, code VARCHAR(40) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E564F0BFF88AD7B9 ON statut (prejudice_id)');
        $this->addSql('CREATE INDEX IDX_E564F0BF79E92E8C ON statut (emetteur_id)');
        $this->addSql('CREATE TABLE tracking (id INT NOT NULL, account_id INT NOT NULL, event VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A87C621C9B6B5FBA ON tracking (account_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, adresse_id INT DEFAULT NULL, personne_physique_id INT DEFAULT NULL, personne_morale_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, date_changement_mdp DATE DEFAULT NULL, is_verified BOOLEAN NOT NULL, mnemo VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, grade VARCHAR(255) DEFAULT NULL, is_personne_morale BOOLEAN DEFAULT false NOT NULL, active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6494DE7DC5C ON "user" (adresse_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64954472AC9 ON "user" (personne_physique_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64935FE3BF6 ON "user" (personne_morale_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED7288B5CD FOREIGN KEY (qualite_requerant_id) REFERENCES qualite_requerant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDD7035A02 FOREIGN KEY (receveur_attestation_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED484A214 FOREIGN KEY (service_enqueteur_id) REFERENCES service_enqueteur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDBF396750 FOREIGN KEY (id) REFERENCES prejudice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_morale ADD CONSTRAINT FK_56031D2ABCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A239194ABF FOREIGN KEY (civilite_id) REFERENCES civilite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2A6338570 FOREIGN KEY (qualite_id) REFERENCES qualite_requerant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2BCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT FK_39465C1F4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT FK_39465C1FBCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BFF88AD7B9 FOREIGN KEY (prejudice_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF79E92E8C FOREIGN KEY (emetteur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tracking ADD CONSTRAINT FK_A87C621C9B6B5FBA FOREIGN KEY (account_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D6494DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64954472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64935FE3BF6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE adresse_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE categorie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE civilite_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE document_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE liasse_documentaire_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE personne_morale_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE personne_physique_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prejudice_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE qualite_requerant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_enqueteur_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE statut_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tracking_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4DE7DC5C');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED7288B5CD');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDD7035A02');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED484A214');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDBF396750');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76BCC8DD14');
        $this->addSql('ALTER TABLE personne_morale DROP CONSTRAINT FK_56031D2ABCC8DD14');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A239194ABF');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2A6338570');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2BCC8DD14');
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT FK_39465C1F4A93DAA5');
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT FK_39465C1FBCC8DD14');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BFF88AD7B9');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF79E92E8C');
        $this->addSql('ALTER TABLE tracking DROP CONSTRAINT FK_A87C621C9B6B5FBA');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D6494DE7DC5C');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64954472AC9');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64935FE3BF6');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE bris_porte');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE civilite');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE liasse_documentaire');
        $this->addSql('DROP TABLE personne_morale');
        $this->addSql('DROP TABLE personne_physique');
        $this->addSql('DROP TABLE prejudice');
        $this->addSql('DROP TABLE qualite_requerant');
        $this->addSql('DROP TABLE service_enqueteur');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE tracking');
        $this->addSql('DROP TABLE "user"');
    }
}
