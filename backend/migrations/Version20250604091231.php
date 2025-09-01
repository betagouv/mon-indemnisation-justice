<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604091231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattacher la personne_physique à un code postal de naissance';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_departements ALTER date_derniere_maj SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE geo_pays ALTER date_derniere_maj SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE personne_physique ADD code_postal_naissance_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2EF5A5513 FOREIGN KEY (code_postal_naissance_id) REFERENCES geo_codes_postaux (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5C2B29A2EF5A5513 ON personne_physique (code_postal_naissance_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2EF5A5513');
        $this->addSql('DROP INDEX IDX_5C2B29A2EF5A5513');
        $this->addSql('ALTER TABLE personne_physique DROP code_postal_naissance_id');
    }
}
