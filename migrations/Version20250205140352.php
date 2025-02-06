<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205140352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du rédacteur attribué au dossier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD redacteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED764D0490 FOREIGN KEY (redacteur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BC580EED764D0490 ON bris_porte (redacteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED764D0490');
        $this->addSql('DROP INDEX IDX_BC580EED764D0490');
        $this->addSql('ALTER TABLE bris_porte DROP redacteur_id');
    }
}
