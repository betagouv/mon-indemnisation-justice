<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909131407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Conversion de la table civilite en champs enum';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne_physique ADD civilite VARCHAR(3)');
        $this->addSql(<<<SQL
UPDATE personne_physique
SET civilite = c.mnemo
FROM (
    select id, mnemo from civilite 
) c
WHERE civilite_id = c.id
SQL);
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT fk_5c2b29a239194abf');
        $this->addSql('DROP INDEX idx_5c2b29a239194abf');
        $this->addSql('ALTER TABLE personne_physique DROP civilite_id');
        $this->addSql('DROP SEQUENCE civilite_id_seq CASCADE');
        $this->addSql('DROP TABLE civilite');
        $this->addSql('ALTER TABLE requerants DROP mnemo');
        $this->addSql('ALTER TABLE requerants DROP fonction');
        $this->addSql('ALTER TABLE requerants DROP titre');
        $this->addSql('ALTER TABLE requerants DROP grade');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE civilite_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE civilite (id SERIAL NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_2c4c1bd677153098 ON civilite (code)');
        $this->addSql('ALTER TABLE personne_physique ADD civilite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique DROP civilite');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT fk_5c2b29a239194abf FOREIGN KEY (civilite_id) REFERENCES civilite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5c2b29a239194abf ON personne_physique (civilite_id)');
        $this->addSql('ALTER TABLE requerants ADD mnemo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE requerants ADD fonction VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE requerants ADD titre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE requerants ADD grade VARCHAR(255) DEFAULT NULL');
    }
}
