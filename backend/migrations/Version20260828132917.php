<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260828132917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renomme avocats.raison_sociale en avocats.cabinet et ajoute DemandeDysfonctionnement '
            .'(partie dysfonctionnement de Dossier, sur le modèle de BrisPorte)';
    }

    public function up(Schema $schema): void
    {
        // avocats.raison_sociale -> avocats.cabinet : c'est le nom du cabinet, pas une raison sociale
        $this->addSql('ALTER TABLE avocats RENAME COLUMN raison_sociale TO cabinet');

        $this->addSql('CREATE TABLE demandes_dysfonctionnement (id UUID NOT NULL, profil VARCHAR(16) DEFAULT NULL, identite_nom VARCHAR(255) DEFAULT NULL, identite_prenom VARCHAR(255) DEFAULT NULL, identite_courriel VARCHAR(255) DEFAULT NULL, identite_cabinet VARCHAR(255) DEFAULT NULL, identite_barreau VARCHAR(255) DEFAULT NULL, test_eligibilite_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_759CCFF49450CE1E ON demandes_dysfonctionnement (test_eligibilite_id)');
        $this->addSql('ALTER TABLE demandes_dysfonctionnement ADD CONSTRAINT FK_759CCFF49450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES dysfonctionnement_test_eligibilite (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE dossiers ADD demande_dysfonctionnement_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E48DE0D6B FOREIGN KEY (demande_dysfonctionnement_id) REFERENCES demandes_dysfonctionnement (id) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A38E22E48DE0D6B ON dossiers (demande_dysfonctionnement_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E48DE0D6B');
        $this->addSql('DROP INDEX UNIQ_A38E22E48DE0D6B');
        $this->addSql('ALTER TABLE dossiers DROP demande_dysfonctionnement_id');
        $this->addSql('ALTER TABLE demandes_dysfonctionnement DROP CONSTRAINT FK_759CCFF49450CE1E');
        $this->addSql('DROP TABLE demandes_dysfonctionnement');

        $this->addSql('ALTER TABLE avocats RENAME COLUMN cabinet TO raison_sociale');
    }
}
