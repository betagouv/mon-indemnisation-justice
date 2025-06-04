<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528151444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rendre geo_departement synchronisable avec les données data.gouv';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_departements ADD date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL');
        $this->addSql('ALTER TABLE geo_departements ADD est_actif BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE geo_pays ALTER date_derniere_maj SET DEFAULT \'NOW()\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_departements DROP date_derniere_maj');
        $this->addSql('ALTER TABLE geo_departements DROP est_actif');
        $this->addSql('ALTER TABLE geo_pays ALTER date_derniere_maj SET DEFAULT \'2025-05-28 14:54:41.94674\'');
    }
}
