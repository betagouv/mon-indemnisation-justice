<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261027091031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la table signature_requetes_requerant';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE signature_requetes_requerant (id UUID NOT NULL, identifiant_exterieur VARCHAR(255) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_signature TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, dossier_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_45CF7189611C0C56 ON signature_requetes_requerant (dossier_id)');
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD CONSTRAINT FK_45CF7189611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossiers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP CONSTRAINT FK_45CF7189611C0C56');
        $this->addSql('DROP TABLE signature_requetes_requerant');
    }
}
