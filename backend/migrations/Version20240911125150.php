<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911125150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table statut';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<SQL
UPDATE bris_porte
SET date_declaration = ebp.date_declaration
FROM (
    SELECT
    DISTINCT ON (bris_porte_id) prejudice_id AS bris_porte_id,
    s.date AS date_declaration
    FROM statut s
    WHERE s.code = 'CONSTITUE'
    ORDER BY bris_porte_id, date DESC
) ebp
WHERE ebp.bris_porte_id = id
SQL);
        $this->addSql('DROP SEQUENCE statut_id_seq CASCADE');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bff88ad7b9');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf3414710b');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf4a93daa5');
        $this->addSql('DROP TABLE statut');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE statut_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE statut (id SERIAL NOT NULL, prejudice_id INT NOT NULL, requerant_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, code VARCHAR(40) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_e564f0bf3414710b ON statut (agent_id)');
        $this->addSql('CREATE INDEX idx_e564f0bf4a93daa5 ON statut (requerant_id)');
        $this->addSql('CREATE INDEX idx_e564f0bff88ad7b9 ON statut (prejudice_id)');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bff88ad7b9 FOREIGN KEY (prejudice_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bf3414710b FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bf4a93daa5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
