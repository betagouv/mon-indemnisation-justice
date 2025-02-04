<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250120101221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jour de la table `agents` pour intégrer les données issues de ProConnect (OIDC)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF3414710B');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX sessions_sess_lifetime_idx RENAME TO sess_lifetime_idx');
        // Le changement pour ProConnect étant irréversible, on doit supprimer les comptes existants
        $this->addSql('DELETE FROM agents');
        $this->addSql('DROP INDEX uniq_agent_email');
        $this->addSql('ALTER TABLE agents ADD identifiant VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE agents ADD uid VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE agents ADD fournisseur_identite VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE agents ADD categorie_agent VARCHAR(255)');
        $this->addSql('ALTER TABLE agents ADD donnes_authentification TEXT');
        $this->addSql('ALTER TABLE agents ADD est_valide BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE agents DROP mot_de_passe');
        $this->addSql('ALTER TABLE agents DROP date_changement_mdp');
        $this->addSql('ALTER TABLE agents DROP est_actif');
        $this->addSql('ALTER TABLE agents DROP jeton_verification');
        $this->addSql('COMMENT ON COLUMN agents.donnes_authentification IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE UNIQUE INDEX uniq_agent_identifiant ON agents (identifiant)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX sess_lifetime_idx RENAME TO sessions_sess_lifetime_idx');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT fk_71671fcf3414710b');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT fk_71671fcf3414710b FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX uniq_agent_identifiant');
        $this->addSql('ALTER TABLE agents ADD mot_de_passe VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents ADD date_changement_mdp DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE agents ADD est_actif BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE agents ADD jeton_verification VARCHAR(12) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents DROP identifiant');
        $this->addSql('ALTER TABLE agents DROP uid');
        $this->addSql('ALTER TABLE agents DROP organisation');
        $this->addSql('ALTER TABLE agents DROP categorie_agent');
        $this->addSql('ALTER TABLE agents DROP donnes_authentification');
        $this->addSql('ALTER TABLE agents DROP est_valide');
        $this->addSql('CREATE UNIQUE INDEX uniq_agent_email ON agents (email)');
    }
}
