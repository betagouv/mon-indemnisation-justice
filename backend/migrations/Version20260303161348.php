<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303161348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rectifier la clef étrangère brouillons x usager';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brouillons DROP CONSTRAINT FK_2C667C244A93DAA5');
        $this->addSql('ALTER TABLE brouillons ADD CONSTRAINT FK_2C667C244A93DAA5 FOREIGN KEY (requerant_id) REFERENCES usagers (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brouillons DROP CONSTRAINT fk_2c667c244a93daa5');
        $this->addSql('ALTER TABLE brouillons ADD CONSTRAINT fk_2c667c244a93daa5 FOREIGN KEY (requerant_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
