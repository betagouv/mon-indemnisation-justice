<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126205036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Permettre plusieurs requêtes de signature requérant par dossier, et référencer l'actuelle depuis le dossier";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers ADD requete_signature_actuelle_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E4474225F5 FOREIGN KEY (requete_signature_actuelle_id) REFERENCES signature_requetes_requerant (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A38E22E4474225F5 ON dossiers (requete_signature_actuelle_id)');
        $this->addSql('DROP INDEX uniq_45cf7189611c0c56');
        $this->addSql('CREATE INDEX IDX_45CF7189611C0C56 ON signature_requetes_requerant (dossier_id)');
        // Jusqu'ici, un dossier n'avait qu'une requête de signature : elle devient sa requête actuelle
        $this->addSql('UPDATE dossiers d SET requete_signature_actuelle_id = s.id FROM signature_requetes_requerant s WHERE s.dossier_id = d.id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E4474225F5');
        $this->addSql('DROP INDEX UNIQ_A38E22E4474225F5');
        $this->addSql('ALTER TABLE dossiers DROP requete_signature_actuelle_id');
        $this->addSql('DROP INDEX IDX_45CF7189611C0C56');
        // Échoue si un dossier a plusieurs requêtes de signature : supprimer au préalable les requêtes fermées en trop
        $this->addSql('CREATE UNIQUE INDEX uniq_45cf7189611c0c56 ON signature_requetes_requerant (dossier_id)');
    }
}
