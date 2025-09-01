<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602151306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table geo_communes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE geo_communes (code VARCHAR(5) NOT NULL, departement_code VARCHAR(3) NOT NULL, nom VARCHAR(255) NOT NULL, code_postaux TEXT NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX IDX_B2E795046A333750 ON geo_communes (departement_code)');
        $this->addSql('COMMENT ON COLUMN geo_communes.code_postaux IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE geo_communes ADD CONSTRAINT FK_B2E795046A333750 FOREIGN KEY (departement_code) REFERENCES geo_departements (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_communes DROP CONSTRAINT FK_B2E795046A333750');
        $this->addSql('DROP TABLE geo_communes');
    }
}
