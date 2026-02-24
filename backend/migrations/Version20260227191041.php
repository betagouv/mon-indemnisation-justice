<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227191041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la table brouillons';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE brouillons (id UUID NOT NULL, requerant_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donnees JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2C667C244A93DAA5 ON brouillons (requerant_id)');
        $this->addSql('CREATE INDEX IDX_2C667C243414710B ON brouillons (agent_id)');
        $this->addSql('COMMENT ON COLUMN brouillons.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN brouillons.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE brouillons ADD CONSTRAINT FK_2C667C244A93DAA5 FOREIGN KEY (requerant_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE brouillons ADD CONSTRAINT FK_2C667C243414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brouillons DROP CONSTRAINT FK_2C667C244A93DAA5');
        $this->addSql('ALTER TABLE brouillons DROP CONSTRAINT FK_2C667C243414710B');
        $this->addSql('DROP TABLE brouillons');
    }
}
