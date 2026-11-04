<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261104165442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Créer les tables de suivi d'envoi de courriel";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE courriels (id UUID NOT NULL, type VARCHAR(255) NOT NULL, recepteur VARCHAR(255) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_expedition TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_reception TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_rejet TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_ouverture TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_clic TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, declaration_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F9F24E5EC06258A3 ON courriels (declaration_id)');
        $this->addSql('CREATE TABLE courriels_evenements (id UUID NOT NULL, type VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, contexte JSON DEFAULT NULL, envoi_courriel_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_70DE01343EB49DDE ON courriels_evenements (envoi_courriel_id)');
        $this->addSql('ALTER TABLE courriels ADD CONSTRAINT FK_F9F24E5EC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE courriels_evenements ADD CONSTRAINT FK_70DE01343EB49DDE FOREIGN KEY (envoi_courriel_id) REFERENCES courriels (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE courriels DROP CONSTRAINT FK_F9F24E5EC06258A3');
        $this->addSql('ALTER TABLE courriels_evenements DROP CONSTRAINT FK_70DE01343EB49DDE');
        $this->addSql('DROP TABLE courriels');
        $this->addSql('DROP TABLE courriels_evenements');
    }
}
