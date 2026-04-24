<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424062101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relier les déclarations FDO à leur bris de porte correspondant';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_74ec5cf7611c0c56');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD bris_porte_id UUID DEFAULT NULL');
        $this->addSql(
            <<<SQL
UPDATE declarations_fdo_bris_porte
SET bris_porte_id = bp.bris_porte_id
FROM (
    SELECT bp.id as bris_porte_id, bp.declaration_id
    FROM bris_porte bp
    WHERE declaration_id IS NOT NULL
     ) bp
WHERE bp.declaration_id = declarations_fdo_bris_porte.id
SQL
        );
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP dossier_id');
        $this->addSql('ALTER TABLE bris_porte DROP declaration_id');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.bris_porte_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73C487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74EC5CF73C487237 ON declarations_fdo_bris_porte (bris_porte_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE personne_morale_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE personne_physique_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dossiers_hors_ligne (date_depot DATE DEFAULT NULL, date_operation DATE DEFAULT NULL, type_fdo VARCHAR(255) DEFAULT NULL, type_attestation VARCHAR(255) DEFAULT NULL, etat_dossier VARCHAR(255) DEFAULT NULL, date_decision DATE DEFAULT NULL, initiales_redacteur VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL)');
        $this->addSql('DROP TABLE _cache_limitateur_connexion');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73C487237');
        $this->addSql('DROP INDEX UNIQ_74EC5CF73C487237');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP bris_porte_id');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT fk_afedb022611c0c56 FOREIGN KEY (dossier_id) REFERENCES dossiers (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_74ec5cf7611c0c56 ON declarations_fdo_bris_porte (dossier_id)');
    }
}
