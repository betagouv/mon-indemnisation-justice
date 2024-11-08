<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108095714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattachement du département au dossier de bris de porte';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD departement_code VARCHAR(3)');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED6A333750 FOREIGN KEY (departement_code) REFERENCES geo_departements (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BC580EED6A333750 ON bris_porte (departement_code)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED6A333750');
        $this->addSql('DROP INDEX IDX_BC580EED6A333750');
        $this->addSql('ALTER TABLE bris_porte DROP departement_code');
    }
}
