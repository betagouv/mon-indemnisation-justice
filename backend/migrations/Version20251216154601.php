<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216154601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brouillons_declaration_fdo_bris_porte (id UUID NOT NULL, agent_id INT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donnees JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C88D0A73414710B ON brouillons_declaration_fdo_bris_porte (agent_id)');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE coordonnees_requerant (id SERIAL NOT NULL, civilite VARCHAR(3) DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, courriel VARCHAR(255) NOT NULL, PRIMARY KEY(id))');

        $this->addSql('ALTER TABLE brouillons_declaration_fdo_bris_porte ADD CONSTRAINT FK_5C88D0A73414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD coordonnees_requerant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD date_suppression TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP infos_requerant');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP precisions_requerant');
        $this->addSql('COMMENT ON COLUMN declarations_fdo_bris_porte.date_suppression IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73E76DA05 FOREIGN KEY (coordonnees_requerant_id) REFERENCES coordonnees_requerant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_74EC5CF73E76DA05 ON declarations_fdo_bris_porte (coordonnees_requerant_id)');
        $this->addSql('ALTER INDEX idx_afedb0224de7dc5c RENAME TO IDX_74EC5CF74DE7DC5C');
        $this->addSql('ALTER INDEX idx_afedb0221624bcd2 RENAME TO IDX_74EC5CF71624BCD2');
        $this->addSql('ALTER INDEX uniq_afedb022611c0c56 RENAME TO UNIQ_74EC5CF7611C0C56');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73E76DA05');
        $this->addSql('ALTER TABLE brouillons_declaration_fdo_bris_porte DROP CONSTRAINT FK_5C88D0A73414710B');
        $this->addSql('DROP TABLE brouillons_declaration_fdo_bris_porte');
        $this->addSql('DROP TABLE coordonnees_requerant');
        $this->addSql('DROP INDEX IDX_74EC5CF73E76DA05');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD infos_requerant JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD precisions_requerant TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP coordonnees_requerant_id');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP date_suppression');
        $this->addSql('ALTER INDEX uniq_74ec5cf7611c0c56 RENAME TO uniq_afedb022611c0c56');
        $this->addSql('ALTER INDEX idx_74ec5cf74de7dc5c RENAME TO idx_afedb0224de7dc5c');
        $this->addSql('ALTER INDEX idx_74ec5cf71624bcd2 RENAME TO idx_afedb0221624bcd2');
    }
}
