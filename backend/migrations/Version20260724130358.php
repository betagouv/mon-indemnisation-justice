<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260724130358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Créer les affectations d'agents des FDO";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fdo_affectations (date_affectation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_mutation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, agent_id INT NOT NULL, etablissement_id INT NOT NULL, PRIMARY KEY (agent_id, etablissement_id))');
        $this->addSql('CREATE INDEX IDX_C56F01463414710B ON fdo_affectations (agent_id)');
        $this->addSql('CREATE INDEX IDX_C56F0146FF631228 ON fdo_affectations (etablissement_id)');
        $this->addSql('ALTER TABLE fdo_affectations ADD CONSTRAINT FK_C56F01463414710B FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE fdo_affectations ADD CONSTRAINT FK_C56F0146FF631228 FOREIGN KEY (etablissement_id) REFERENCES agents (id) NOT DEFERRABLE');
        $this->addSql('DROP INDEX idx_code_postal');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fdo_affectations DROP CONSTRAINT FK_C56F01463414710B');
        $this->addSql('ALTER TABLE fdo_affectations DROP CONSTRAINT FK_C56F0146FF631228');
        $this->addSql('DROP TABLE fdo_affectations');
        $this->addSql('CREATE INDEX idx_code_postal ON geo_codes_postaux (code_postal)');
    }
}
