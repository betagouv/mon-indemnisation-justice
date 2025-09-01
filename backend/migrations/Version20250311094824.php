<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250311094824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déclaration de la table dossier_courriers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier_courriers (id SERIAL NOT NULL, dossier_id INT NOT NULL, agent_id INT DEFAULT NULL, requerant_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3FAD5FC611C0C56 ON dossier_courriers (dossier_id)');
        $this->addSql('CREATE INDEX IDX_E3FAD5FC3414710B ON dossier_courriers (agent_id)');
        $this->addSql('CREATE INDEX IDX_E3FAD5FC4A93DAA5 ON dossier_courriers (requerant_id)');
        $this->addSql('COMMENT ON COLUMN dossier_courriers.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT FK_E3FAD5FC611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT FK_E3FAD5FC3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_courriers ADD CONSTRAINT FK_E3FAD5FC4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD courrier_actuel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte DROP numero_parquet');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDE785C776 FOREIGN KEY (courrier_actuel_id) REFERENCES dossier_courriers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDE785C776 ON bris_porte (courrier_actuel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDE785C776');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT FK_E3FAD5FC611C0C56');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT FK_E3FAD5FC3414710B');
        $this->addSql('ALTER TABLE dossier_courriers DROP CONSTRAINT FK_E3FAD5FC4A93DAA5');
        $this->addSql('DROP TABLE dossier_courriers');
        $this->addSql('DROP INDEX UNIQ_BC580EEDE785C776');
        $this->addSql('ALTER TABLE bris_porte ADD numero_parquet VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte DROP courrier_actuel_id');
    }
}
