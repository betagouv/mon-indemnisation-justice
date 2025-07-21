<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721134023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression du champs raccourci';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP raccourci');
        $this->addSql('ALTER INDEX idx_test_elig_requerant RENAME TO IDX_881877C14A93DAA5');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_881877c14a93daa5 RENAME TO idx_test_elig_requerant');
        $this->addSql('ALTER TABLE bris_porte ADD raccourci VARCHAR(20) DEFAULT NULL');
    }
}
