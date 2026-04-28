<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428133710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renforcer la sécurité en empêchant de créer un dossier sans requérant PP ou PM';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT requerant_not_null CHECK (requerant_personne_physique_id IS NOT NULL OR requerant_personne_morale_id IS NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT requerant_not_null');
    }
}
