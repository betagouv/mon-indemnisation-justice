<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250102145007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Correction des dossiers sans état initial';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed484a214');
        $this->addSql('DROP SEQUENCE service_enqueteur_id_seq CASCADE');
        $this->addSql('DROP TABLE service_enqueteur');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eedd7035a02');
        $this->addSql('DROP INDEX uniq_bc580eed484a214');
        $this->addSql('DROP INDEX idx_bc580eedd7035a02');
        $this->addSql('ALTER TABLE bris_porte DROP receveur_attestation_id');
        $this->addSql('ALTER TABLE bris_porte DROP service_enqueteur_id');
        $this->addSql('ALTER TABLE bris_porte DROP is_erreur_porte');
        $this->addSql('ALTER TABLE bris_porte DROP identite_personne_recherchee');
        $this->addSql('ALTER TABLE bris_porte DROP nom_remise_attestation');
        $this->addSql('ALTER TABLE bris_porte DROP prenom_remise_attestation');
        $this->addSql('ALTER TABLE bris_porte DROP date_attestation_information');
        $this->addSql('ALTER TABLE bris_porte DROP date_declaration');
        $this->addSql('ALTER TABLE bris_porte DROP note');
        $this->addSql('ALTER TABLE bris_porte DROP motivation_proposition');
        $this->addSql(<<<SQL
INSERT INTO dossier_etats (dossier_id, requerant_id, etat, date)
SELECT id, requerant_id, 'DOSSIER_INITIE', date_creation
FROM bris_porte
WHERE etat_actuel_id IS NULL
SQL);
        $this->addSql(<<<SQL
UPDATE bris_porte
SET etat_actuel_id = de.id
FROM dossier_etats de
WHERE
    de.dossier_id = bris_porte.id AND de.etat = 'DOSSIER_INITIE';
SQL);
        $this->addSql(<<<SQL
UPDATE bris_porte
SET qualite_requerant = d.qualite_requerant, precision_requerant = d.precision_requerant
FROM (
    SELECT d.id, pp.qualite_requerant, pp.precision as precision_requerant
    FROM bris_porte d
        INNER JOIN requerants r ON d.requerant_id = r.id
        INNER JOIN personne_physique pp ON r.personne_physique_id = pp.id
    WHERE d.qualite_requerant IS NULL AND pp.qualite_requerant IS NOT NULL
) d
WHERE bris_porte.id = d.id
SQL);
        $this->addSql('ALTER TABLE eligibilite_tests ALTER date_soumission TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN eligibilite_tests.date_soumission IS NULL');
        $this->addSql('ALTER TABLE personne_physique DROP "precision"');
        $this->addSql('ALTER TABLE personne_physique DROP qualite_requerant');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE service_enqueteur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE service_enqueteur (id SERIAL NOT NULL, nom VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, courriel VARCHAR(255) DEFAULT NULL, numero_pv VARCHAR(255) DEFAULT NULL, juridiction VARCHAR(255) DEFAULT NULL, magistrat VARCHAR(255) DEFAULT NULL, numero_parquet VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE bris_porte ADD receveur_attestation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD service_enqueteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD is_erreur_porte BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD identite_personne_recherchee VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD nom_remise_attestation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD prenom_remise_attestation VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD date_attestation_information DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD date_declaration TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD motivation_proposition TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed484a214 FOREIGN KEY (service_enqueteur_id) REFERENCES service_enqueteur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eedd7035a02 FOREIGN KEY (receveur_attestation_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_bc580eed484a214 ON bris_porte (service_enqueteur_id)');
        $this->addSql('CREATE INDEX idx_bc580eedd7035a02 ON bris_porte (receveur_attestation_id)');
        $this->addSql('ALTER TABLE personne_physique ADD "precision" VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD qualite_requerant VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER date_soumission TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN eligibilite_tests.date_soumission IS \'(DC2Type:datetime_immutable)\'');
    }
}
