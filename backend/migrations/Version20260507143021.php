<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507143021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rajouter la colonne est_personne_morale dans la table dossiers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers ADD est_personne_morale BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('UPDATE dossiers SET est_personne_morale = true WHERE requerant_personne_morale_id IS NOT NULL AND est_personne_morale = false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers DROP est_personne_morale');
    }
}
