<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240826100554 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table "agents"';
    }

    public function up(Schema $schema): void
    {
        // Purge générale
        $this->addSql('TRUNCATE TABLE "user" CASCADE');
        $this->addSql('CREATE TABLE agents (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(30) NOT NULL, roles TEXT NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, date_changement_mdp DATE DEFAULT NULL, est_actif BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_agent_email ON agents (email)');
        $this->addSql('COMMENT ON COLUMN agents.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE "user" ALTER roles SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE agents');
        $this->addSql('ALTER TABLE "user" ALTER roles DROP NOT NULL');
    }
}
