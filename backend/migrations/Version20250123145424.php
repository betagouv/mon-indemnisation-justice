<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250123145424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table agent_fournisseurs_identites';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE agent_fournisseurs_identites (uid VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, est_reseau_interne BOOLEAN NOT NULL, est_actif BOOLEAN NOT NULL, url_decouverte VARCHAR(255) NOT NULL, domaines JSON NOT NULL, categorie_agent VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uid))');
        $this->addSql('ALTER TABLE agents ADD fournisseur_identite_uid VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents DROP fournisseur_identite');
        $this->addSql('ALTER TABLE agents ALTER donnes_authentification TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN agents.donnes_authentification IS NULL');
        $this->addSql('ALTER TABLE agents ADD CONSTRAINT FK_9596AB6E2CCC2389 FOREIGN KEY (fournisseur_identite_uid) REFERENCES agent_fournisseurs_identites (uid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_9596AB6E2CCC2389 ON agents (fournisseur_identite_uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agents DROP CONSTRAINT FK_9596AB6E2CCC2389');
        $this->addSql('DROP TABLE agent_fournisseurs_identites');
        $this->addSql('DROP INDEX IDX_9596AB6E2CCC2389');
        $this->addSql('ALTER TABLE agents ADD fournisseur_identite VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE agents DROP fournisseur_identite_uid');
        $this->addSql('ALTER TABLE agents ALTER donnes_authentification TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN agents.donnes_authentification IS \'(DC2Type:simple_array)\'');
    }
}
