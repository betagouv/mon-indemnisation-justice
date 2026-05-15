<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515101417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter la table administrations pour remplacer agent_fournisseurs_identites et institutions_securite_publique';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_9596ab6e2ccc2389');
        $this->addSql('ALTER TABLE agents DROP CONSTRAINT fk_9596ab6e2ccc2389');
        $this->addSql('ALTER TABLE agents DROP fournisseur_identite_uid');
        $this->addSql('DROP TABLE agent_fournisseurs_identites');
        $this->addSql('CREATE TABLE administrations (code VARCHAR(2) NOT NULL, siret VARCHAR(14) NOT NULL, date_integration TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (code))');
        $this->addSql(
            <<<SQL
INSERT INTO administrations (code, siret, date_integration)
VALUES ('MJ', '11001001400014', TO_TIMESTAMP('2024-06-30', 'YYYY-MM-DD')),
       ('PN', '12001501100014', TO_TIMESTAMP('2024-11-07', 'YYYY-MM-DD')),
       ('GN', '15700001900461', TO_TIMESTAMP('2025-03-20', 'YYYY-MM-DD')),
       ('PP', '17750151700011', null)
SQL
        );

        $this->addSql('DROP TABLE institutions_securite_publique');
        $this->addSql('ALTER TABLE agents ADD administration_code VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents DROP administration');
        $this->addSql('ALTER TABLE agents DROP administration_siret');
        $this->addSql('ALTER TABLE agents ADD CONSTRAINT FK_9596AB6EFB4274AB FOREIGN KEY (administration_code) REFERENCES administrations (code) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_9596AB6EFB4274AB ON agents (administration_code)');
        $this->addSql('ALTER TABLE bris_porte RENAME COLUMN type_institution_securite_publique TO type_administration');
        $this->addSql("UPDATE document SET meta_donnees = (meta_donnees::jsonb - 'typeInstitutionSecuritePublique') || jsonb_build_object('typeAdministration', meta_donnees::jsonb->'typeInstitutionSecuritePublique') WHERE meta_donnees IS NOT NULL AND meta_donnees::jsonb ?? 'typeInstitutionSecuritePublique'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agent_fournisseurs_identites (uid VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, est_reseau_interne BOOLEAN NOT NULL, est_actif BOOLEAN NOT NULL, url_decouverte VARCHAR(255) NOT NULL, domaines JSON NOT NULL, administration VARCHAR(5) DEFAULT NULL, PRIMARY KEY (uid))');
        $this->addSql('CREATE TABLE institutions_securite_publique (type VARCHAR(2) NOT NULL, date_integration DATE DEFAULT NULL, PRIMARY KEY (type))');
        $this->addSql('DROP TABLE administrations');
        $this->addSql('ALTER TABLE agents DROP CONSTRAINT FK_9596AB6EFB4274AB');
        $this->addSql('DROP INDEX IDX_9596AB6EFB4274AB');
        $this->addSql('ALTER TABLE agents ADD administration VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents ADD fournisseur_identite_uid VARCHAR DEFAULT NULL');
        $this->addSql('ALTER TABLE agents ADD administration_siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents DROP administration_code');
        $this->addSql('ALTER TABLE agents ADD CONSTRAINT fk_9596ab6e2ccc2389 FOREIGN KEY (fournisseur_identite_uid) REFERENCES agent_fournisseurs_identites (uid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9596ab6e2ccc2389 ON agents (fournisseur_identite_uid)');
        $this->addSql('ALTER TABLE bris_porte RENAME COLUMN type_administration TO type_institution_securite_publique');
        $this->addSql("UPDATE document SET meta_donnees = (meta_donnees::jsonb - 'typeAdministration') || jsonb_build_object('typeInstitutionSecuritePublique', meta_donnees::jsonb->'typeAdministration') WHERE meta_donnees IS NOT NULL AND meta_donnees::jsonb ?? 'typeAdministration'");
    }
}
