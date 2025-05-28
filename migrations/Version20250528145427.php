<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528145427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rendre geo_pays synchronisable avec les données data.gouv';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_pays ADD code_insee VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_pays ADD date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL');
        $this->addSql('ALTER TABLE geo_pays ADD est_actif BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE geo_pays DROP est_france');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_pays ADD est_france BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE geo_pays DROP code_insee');
        $this->addSql('ALTER TABLE geo_pays DROP date_derniere_maj');
        $this->addSql('ALTER TABLE geo_pays DROP est_actif');
    }
}
