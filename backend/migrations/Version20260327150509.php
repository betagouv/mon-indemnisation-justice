<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260327150509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter le type et l'adresse de personne morale";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_morales ADD type VARCHAR(32) DEFAULT \'ENTREPRISE_PRIVEE\' NOT NULL');
        $this->addSql('ALTER TABLE personnes_morales ADD adresse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personnes_morales ADD CONSTRAINT FK_CD3370B24DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CD3370B24DE7DC5C ON personnes_morales (adresse_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_morales DROP CONSTRAINT FK_CD3370B24DE7DC5C');
        $this->addSql('DROP INDEX IDX_CD3370B24DE7DC5C');
        $this->addSql('ALTER TABLE personnes_morales DROP adresse_id');
        $this->addSql('ALTER TABLE personnes_morales DROP type');
    }
}
