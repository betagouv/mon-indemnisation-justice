<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127161831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les références bancaires (RIB / IBAN) et le référentiel des établissements bancaires';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE etablissements_bancaires (code_banque VARCHAR(5) NOT NULL, nom VARCHAR(255) NOT NULL, logo VARCHAR(100) DEFAULT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, date_derniere_maj TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY (code_banque))');
        $this->addSql('CREATE TABLE references_bancaires (id UUID NOT NULL, type VARCHAR(4) NOT NULL, code_banque VARCHAR(5) DEFAULT NULL, code_guichet VARCHAR(5) DEFAULT NULL, numero_compte VARCHAR(11) DEFAULT NULL, cle_rib VARCHAR(2) DEFAULT NULL, iban VARCHAR(34) DEFAULT NULL, nom_banque VARCHAR(255) DEFAULT NULL, titulaire_du_compte VARCHAR(255) NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, etablissement_code_banque VARCHAR(5) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2CBF3B89E946D132 ON references_bancaires (etablissement_code_banque)');
        $this->addSql('ALTER TABLE references_bancaires ADD CONSTRAINT FK_2CBF3B89E946D132 FOREIGN KEY (etablissement_code_banque) REFERENCES etablissements_bancaires (code_banque) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE dossiers ADD reference_bancaire_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E46538FF7E FOREIGN KEY (reference_bancaire_id) REFERENCES references_bancaires (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_A38E22E46538FF7E ON dossiers (reference_bancaire_id)');
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD reference_bancaire_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD CONSTRAINT FK_45CF71896538FF7E FOREIGN KEY (reference_bancaire_id) REFERENCES references_bancaires (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_45CF71896538FF7E ON signature_requetes_requerant (reference_bancaire_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP CONSTRAINT FK_45CF71896538FF7E');
        $this->addSql('DROP INDEX IDX_45CF71896538FF7E');
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP reference_bancaire_id');
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E46538FF7E');
        $this->addSql('DROP INDEX IDX_A38E22E46538FF7E');
        $this->addSql('ALTER TABLE dossiers DROP reference_bancaire_id');
        $this->addSql('DROP TABLE references_bancaires');
        $this->addSql('DROP TABLE etablissements_bancaires');
    }
}
