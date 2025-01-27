<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250127152715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agent_fournisseurs_identites RENAME COLUMN categorie_agent TO administration');
        $this->addSql('ALTER TABLE agents RENAME COLUMN categorie_agent TO administration');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agent_fournisseurs_identites RENAME COLUMN administration TO categorie_agent');
        $this->addSql('ALTER TABLE agents RENAME COLUMN administration TO categorie_agent');
    }
}
