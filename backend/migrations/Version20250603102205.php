<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603102205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relier les adresses aux geo_communes et séparer les codes postaux des communes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE geo_codes_postaux (id SERIAL NOT NULL, code_commune VARCHAR(5) NOT NULL, code_postal VARCHAR(5) NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFDB17BBDA459572 ON geo_codes_postaux (code_commune)');
        $this->addSql('CREATE INDEX idx_code_postal ON geo_codes_postaux (code_postal)');
        $this->addSql('CREATE UNIQUE INDEX unique_code_insee_postal ON geo_codes_postaux (code_postal, code_commune)');
        $this->addSql('ALTER TABLE geo_codes_postaux ADD CONSTRAINT FK_CFDB17BBDA459572 FOREIGN KEY (code_commune) REFERENCES geo_communes (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE adresse ADD commune_code VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_communes DROP code_postaux');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_codes_postaux DROP CONSTRAINT FK_CFDB17BBDA459572');
        $this->addSql('DROP TABLE geo_codes_postaux');
        $this->addSql('ALTER TABLE adresse DROP commune_code');
        $this->addSql('ALTER TABLE geo_communes ADD code_postaux TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN geo_communes.code_postaux IS \'(DC2Type:simple_array)\'');
    }
}
