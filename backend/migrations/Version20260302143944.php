<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302143944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déplacer les données de bris de porte dans une table dédiée';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bris_porte (id UUID NOT NULL, test_eligibilite_id INT DEFAULT NULL, declaration_id UUID DEFAULT NULL, adresse_id INT DEFAULT NULL, rapport_au_logement VARCHAR(16) DEFAULT NULL, precision_rapport_au_logement VARCHAR(255) DEFAULT NULL, description_requerant TEXT DEFAULT NULL, type_institution_securite_publique VARCHAR(2) DEFAULT NULL, type_attestation VARCHAR(20) DEFAULT NULL, date_operation DATE DEFAULT NULL, est_porte_blindee BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EED9450CE1E ON bris_porte (test_eligibilite_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDC06258A3 ON bris_porte (declaration_id)');
        $this->addSql('CREATE INDEX IDX_BC580EED4DE7DC5C ON bris_porte (adresse_id)');
        $this->addSql('COMMENT ON COLUMN bris_porte.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bris_porte.declaration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE dossiers ADD COLUMN bris_porte_id UUID');

        $this->addSql('ALTER TABLE bris_porte ADD COLUMN dossier_id integer');

        $this->addSql(
            <<<SQL
WITH bp AS (
    INSERT INTO bris_porte (dossier_id, id, test_eligibilite_id, declaration_id, adresse_id, rapport_au_logement, precision_rapport_au_logement, description_requerant, type_institution_securite_publique, type_attestation, date_operation, est_porte_blindee)
    SELECT
        d.id,
        gen_random_uuid(),
        d.test_eligibilite_id,
        d.declaration_id,
        d.adresse_id,
        CASE
            WHEN d.qualite_requerant = 'PRO' then 'PROPRIETAIRE'
            WHEN d.qualite_requerant = 'LOC' then 'LOCATAIRE'
            WHEN d.qualite_requerant = 'BAI' then 'BAILLEUR'
            ELSE 'AUTRE'
        END AS rapport_au_logement,
        d.precision_requerant AS precision_rapport_au_logement,
        d.description_requerant,
        d.type_institution_securite_publique,
        d.type_attestation,
        d.date_operation_pj,
        d.is_porte_blindee AS est_porte_blindee
    FROM dossiers d
    RETURNING id, dossier_id
)
UPDATE dossiers
SET bris_porte_id  = bp.id
FROM bp
WHERE dossiers.id = bp.dossier_id
SQL
        );

        $this->addSql('ALTER TABLE bris_porte DROP COLUMN dossier_id');

        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED9450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT fk_bc580eed4de7dc5c');
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT fk_bc580eed9450ce1e');
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT fk_bc580eedc06258a3');
        $this->addSql('DROP INDEX idx_a38e22e44de7dc5c');
        $this->addSql('DROP INDEX uniq_a38e22e49450ce1e');
        $this->addSql('DROP INDEX uniq_a38e22e4c06258a3');
        $this->addSql("ALTER TABLE dossiers ADD type VARCHAR(64) NOT NULL DEFAULT 'BRIS_PORTE'");
        $this->addSql('ALTER TABLE dossiers ALTER COLUMN type DROP DEFAULT');

        $this->addSql('DROP VIEW IF EXISTS dossiers_en_ligne');
        $this->addSql('ALTER TABLE dossiers DROP adresse_id');
        $this->addSql('ALTER TABLE dossiers DROP test_eligibilite_id');
        $this->addSql('ALTER TABLE dossiers DROP date_operation_pj');
        $this->addSql('ALTER TABLE dossiers DROP is_porte_blindee');
        $this->addSql('ALTER TABLE dossiers DROP precision_requerant');
        $this->addSql('ALTER TABLE dossiers DROP qualite_requerant');
        $this->addSql('ALTER TABLE dossiers DROP type_institution_securite_publique');
        $this->addSql('ALTER TABLE dossiers DROP description_requerant');
        $this->addSql('ALTER TABLE dossiers DROP type_attestation');
        $this->addSql('ALTER TABLE dossiers DROP declaration_id');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E43C487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A38E22E43C487237 ON dossiers (bris_porte_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs


        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E43C487237');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDC06258A3');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4DE7DC5C');

        $this->addSql('ALTER TABLE dossiers ADD adresse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD test_eligibilite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD declaration_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD date_operation_pj DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD is_porte_blindee BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE dossiers ADD precision_requerant VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD qualite_requerant VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD type_institution_securite_publique VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD description_requerant TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD type_attestation VARCHAR(20) DEFAULT NULL');

        $this->addSql(
            <<<SQL
UPDATE dossiers d
SET
   adresse_id = bp.adresse_id, 
   test_eligibilite_id = bp.test_eligibilite_id, 
   declaration_id = bp.declaration_id, 
   date_operation_pj = bp.date_operation, 
   is_porte_blindee = bp.est_porte_blindee, 
   precision_requerant = bp.precision_rapport_au_logement, 
   qualite_requerant = substr(bp.rapport_au_logement, 0, 3),
   description_requerant = bp.description_requerant,
   type_attestation = bp.type_attestation,
   type_institution_securite_publique = bp.type_institution_securite_publique
FROM (
    SELECT d.id as dossier_id, bp.*
    FROM bris_porte bp
        INNER JOIN dossiers d ON d.bris_porte_id = bp.id
) bp
WHERE d.id = bp.dossier_id
SQL
        );

        $this->addSql('DROP TABLE bris_porte');
        $this->addSql('DROP INDEX UNIQ_A38E22E43C487237');
        $this->addSql('ALTER TABLE dossiers DROP type');
        $this->addSql('ALTER TABLE dossiers DROP bris_porte_id');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT fk_bc580eed4de7dc5c FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT fk_bc580eed9450ce1e FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT fk_bc580eedc06258a3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a38e22e44de7dc5c ON dossiers (adresse_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_a38e22e49450ce1e ON dossiers (test_eligibilite_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_a38e22e4c06258a3 ON dossiers (declaration_id)');
    }
}
