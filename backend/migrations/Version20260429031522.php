<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429031522 extends AbstractMigration
{
    public function getDescription(): string
    {
        // Une personne peut déposer plusieurs dossiers en tant que personne physique
        return "Suppression de l'unicité personne physique <-> personne";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_1eecb5f1a21bd112');
        $this->addSql('CREATE INDEX IDX_1EECB5F1A21BD112 ON personnes_physiques (personne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_1EECB5F1A21BD112');
        $this->addSql('CREATE UNIQUE INDEX uniq_1eecb5f1a21bd112 ON personnes_physiques (personne_id)');
    }
}
