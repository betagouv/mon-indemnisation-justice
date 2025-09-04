<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241108163213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création et association des états du dossier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier_etats (id SERIAL NOT NULL, dossier_id INT NOT NULL, agent_id INT DEFAULT NULL, requerant_id INT DEFAULT NULL, etat VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_71671FCF611C0C56 ON dossier_etats (dossier_id)');
        $this->addSql('CREATE INDEX IDX_71671FCF3414710B ON dossier_etats (agent_id)');
        $this->addSql('CREATE INDEX IDX_71671FCF4A93DAA5 ON dossier_etats (requerant_id)');
        $this->addSql('COMMENT ON COLUMN dossier_etats.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(<<<SQL
INSERT INTO dossier_etats (dossier_id, etat, date, requerant_id)
SELECT
    d.id AS dossier_id, 'DOSSIER_INITIE' AS etat, d.date_creation, d.requerant_id
FROM bris_porte d
UNION ALL
SELECT
    d.id AS dossier_id, 'DOSSIER_DEPOSE' AS etat, d.date_declaration, d.requerant_id
FROM bris_porte d
WHERE d.date_declaration IS NOT null
SQL);
        $this->addSql('ALTER TABLE personne_physique DROP code_securite_sociale');

        // TODO migrer les données depuis les champs date
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT IF EXISTS FK_BC580EEDF90DA413');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF611C0C56');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF3414710B');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF4A93DAA5');
        $this->addSql('DROP TABLE dossier_etats');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_BC580EEDF90DA413');
        $this->addSql('ALTER TABLE personne_physique ADD code_securite_sociale VARCHAR(2) DEFAULT NULL');
    }
}
