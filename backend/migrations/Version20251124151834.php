<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124151834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table des courriers, plus utilisée';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE dossier_courriers_id_seq CASCADE');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT fk_e3fad5fc3414710b');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT fk_e3fad5fc4a93daa5');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT fk_e3fad5fc611c0c56');
        $this->addSql('DROP TABLE dossier_courriers');
        $this->addSql('ALTER TABLE agent_fournisseurs_identites ALTER administration TYPE VARCHAR(5)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE dossier_courriers_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dossier_courriers (id SERIAL NOT NULL, dossier_id INT NOT NULL, agent_id INT DEFAULT NULL, requerant_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e3fad5fc611c0c56 ON dossier_courriers (dossier_id)');
        $this->addSql('CREATE INDEX idx_e3fad5fc4a93daa5 ON dossier_courriers (requerant_id)');
        $this->addSql('CREATE INDEX idx_e3fad5fc3414710b ON dossier_courriers (agent_id)');
        $this->addSql('COMMENT ON COLUMN dossier_courriers.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT fk_e3fad5fc3414710b FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT fk_e3fad5fc4a93daa5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT fk_e3fad5fc611c0c56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE agent_fournisseurs_identites ALTER administration TYPE VARCHAR(255)');
    }
}
