<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702144847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajout de l'entité EtablissementFDO";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fdo_etablissements (id UUID NOT NULL, nom VARCHAR(255) NOT NULL, identifiant VARCHAR(16) NOT NULL, telephone VARCHAR(16) DEFAULT NULL, courriel VARCHAR(100) DEFAULT NULL, administration_code VARCHAR(2) DEFAULT NULL, adresse_id INT DEFAULT NULL, code_postal_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1BB6213DFB4274AB ON fdo_etablissements (administration_code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BB6213D4DE7DC5C ON fdo_etablissements (adresse_id)');
        $this->addSql('CREATE INDEX IDX_1BB6213DB2B59251 ON fdo_etablissements (code_postal_id)');
        $this->addSql('CREATE INDEX unique_nom_etablissement ON fdo_etablissements (nom)');
        $this->addSql('CREATE TABLE fdo_etablissements_code_postaux (etablissement_id UUID NOT NULL, code_postal_id INT NOT NULL, PRIMARY KEY (etablissement_id, code_postal_id))');
        $this->addSql('CREATE INDEX IDX_5E473888FF631228 ON fdo_etablissements_code_postaux (etablissement_id)');
        $this->addSql('CREATE INDEX IDX_5E473888B2B59251 ON fdo_etablissements_code_postaux (code_postal_id)');
        $this->addSql('ALTER TABLE fdo_etablissements ADD CONSTRAINT FK_1BB6213DFB4274AB FOREIGN KEY (administration_code) REFERENCES administrations (code) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE fdo_etablissements ADD CONSTRAINT FK_1BB6213D4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE fdo_etablissements ADD CONSTRAINT FK_1BB6213DB2B59251 FOREIGN KEY (code_postal_id) REFERENCES geo_codes_postaux (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE fdo_etablissements_code_postaux ADD CONSTRAINT FK_5E473888FF631228 FOREIGN KEY (etablissement_id) REFERENCES fdo_etablissements (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE fdo_etablissements_code_postaux ADD CONSTRAINT FK_5E473888B2B59251 FOREIGN KEY (code_postal_id) REFERENCES geo_codes_postaux (id) NOT DEFERRABLE');
        $this->addSql('ALTER INDEX idx_881877c16a333750 RENAME TO IDX_6AD6C7C46A333750');
        $this->addSql('ALTER INDEX idx_881877c14f36f0fc RENAME TO IDX_6AD6C7C44F36F0FC');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fdo_etablissements DROP CONSTRAINT FK_1BB6213DFB4274AB');
        $this->addSql('ALTER TABLE fdo_etablissements DROP CONSTRAINT FK_1BB6213D4DE7DC5C');
        $this->addSql('ALTER TABLE fdo_etablissements DROP CONSTRAINT FK_1BB6213DB2B59251');
        $this->addSql('ALTER TABLE fdo_etablissements_code_postaux DROP CONSTRAINT FK_5E473888FF631228');
        $this->addSql('ALTER TABLE fdo_etablissements_code_postaux DROP CONSTRAINT FK_5E473888B2B59251');
        $this->addSql('DROP TABLE fdo_etablissements');
        $this->addSql('DROP TABLE fdo_etablissements_code_postaux');
        $this->addSql('ALTER INDEX idx_6ad6c7c44f36f0fc RENAME TO idx_881877c14f36f0fc');
        $this->addSql('ALTER INDEX idx_6ad6c7c46a333750 RENAME TO idx_881877c16a333750');
    }
}
