<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212161057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Permettre la pré-déclaration d'agent";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents ALTER date_creation DROP NOT NULL');
        $this->addSql('ALTER INDEX idx_2c4fd3bd3c487237 RENAME TO IDX_2C4FD3BD611C0C56');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_2c4fd3bd611c0c56 RENAME TO idx_2c4fd3bd3c487237');
        $this->addSql('ALTER TABLE agents ALTER date_creation SET NOT NULL');
    }
}
