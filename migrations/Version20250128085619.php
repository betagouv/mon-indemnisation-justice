<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250128085619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champs date_creation à la table agents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM agents');
        $this->addSql('ALTER TABLE agents ADD date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents DROP date_creation');
    }
}
