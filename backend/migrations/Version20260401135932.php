<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401135932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommer la colonne requerant_id en usager_id dans la table eligibilite_tests';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT fk_881877c14a93daa5');
        $this->addSql('DROP INDEX idx_881877c14a93daa5');
        $this->addSql('ALTER TABLE eligibilite_tests RENAME COLUMN requerant_id TO usager_id');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C14F36F0FC FOREIGN KEY (usager_id) REFERENCES usagers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_881877C14F36F0FC ON eligibilite_tests (usager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C14F36F0FC');
        $this->addSql('DROP INDEX IDX_881877C14F36F0FC');
        $this->addSql('ALTER TABLE eligibilite_tests RENAME COLUMN usager_id TO requerant_id');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT fk_881877c14a93daa5 FOREIGN KEY (requerant_id) REFERENCES usagers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_881877c14a93daa5 ON eligibilite_tests (requerant_id)');
    }
}
