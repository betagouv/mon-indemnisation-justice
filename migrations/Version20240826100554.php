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


        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf79e92e8c');
        $this->addSql('DROP INDEX idx_e564f0bf79e92e8c');
        $this->addSql('ALTER TABLE statut ADD requerant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statut ADD agent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statut DROP emetteur_id');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E564F0BF4A93DAA5 ON statut (requerant_id)');
        $this->addSql('CREATE INDEX IDX_E564F0BF3414710B ON statut (agent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF4A93DAA5');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF3414710B');
        $this->addSql('DROP INDEX IDX_E564F0BF4A93DAA5');
        $this->addSql('DROP INDEX IDX_E564F0BF3414710B');
        $this->addSql('ALTER TABLE statut ADD emetteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE statut DROP requerant_id');
        $this->addSql('ALTER TABLE statut DROP agent_id');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bf79e92e8c FOREIGN KEY (emetteur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e564f0bf79e92e8c ON statut (emetteur_id)');
        $this->addSql('DROP TABLE agents');
        $this->addSql('ALTER TABLE "user" ALTER roles DROP NOT NULL');
    }
}
