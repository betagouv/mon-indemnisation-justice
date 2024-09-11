<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240911084834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Conversion de la table qualite_requerant en champs enum';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT fk_5c2b29a2a6338570');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed7288b5cd');
        $this->addSql('ALTER TABLE bris_porte ADD qualite_requerant VARCHAR(3) DEFAULT NULL');
        $this->addSql(<<<SQL
UPDATE bris_porte
SET qualite_requerant = qr.mnemo
FROM (
    SELECT id, mnemo
    FROM qualite_requerant
) qr
WHERE qr.id = qualite_requerant_id
SQL);
        $this->addSql('DROP INDEX idx_bc580eed7288b5cd');
        $this->addSql('ALTER TABLE bris_porte DROP qualite_requerant_id');
        $this->addSql('ALTER TABLE personne_physique ADD qualite_requerant VARCHAR(3) DEFAULT NULL');
        $this->addSql(<<<SQL
UPDATE personne_physique
SET qualite_requerant = qr.mnemo
FROM (
    SELECT id, mnemo
    FROM qualite_requerant
) qr
WHERE qr.id = qualite_id
SQL);
        $this->addSql('DROP INDEX idx_5c2b29a2a6338570');
        $this->addSql('ALTER TABLE personne_physique DROP qualite_id');
        $this->addSql('DROP SEQUENCE qualite_requerant_id_seq CASCADE');
        $this->addSql('DROP TABLE qualite_requerant');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE qualite_requerant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE qualite_requerant (id SERIAL NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_37d0575f77153098 ON qualite_requerant (code)');
        $this->addSql('ALTER TABLE personne_physique ADD qualite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique DROP qualite_requerant');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT fk_5c2b29a2a6338570 FOREIGN KEY (qualite_id) REFERENCES qualite_requerant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5c2b29a2a6338570 ON personne_physique (qualite_id)');
        $this->addSql('ALTER TABLE bris_porte ADD qualite_requerant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte DROP qualite_requerant');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed7288b5cd FOREIGN KEY (qualite_requerant_id) REFERENCES qualite_requerant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_bc580eed7288b5cd ON bris_porte (qualite_requerant_id)');
    }
}
