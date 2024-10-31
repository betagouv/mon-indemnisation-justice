<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240909074057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table prejudice à la faveur de bris_porte indépendante';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eedbf396750');
        $this->addSql('DROP SEQUENCE categorie_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prejudice_id_seq CASCADE');
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT fk_39465c1fbcc8dd14');
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT fk_39465c1f4a93daa5');
        // Dans le cadre de la refacto, on peut purger les tables (plus simple que de transférer les données existantes).
        $this->addSql('TRUNCATE TABLE bris_porte CASCADE');
        $this->addSql('TRUNCATE TABLE prejudice CASCADE');
        $this->addSql('DROP TABLE IF EXISTS prejudice');
        $this->addSql('DROP TABLE IF EXISTS categorie');
        $this->addSql('ALTER TABLE bris_porte ADD requerant_id INT NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD liasse_documentaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD date_declaration DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD reference VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD proposition_indemnisation NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD motivation_proposition TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD raccourci VARCHAR(20) DEFAULT NULL');
        $this->addSql('CREATE SEQUENCE bris_porte_id_seq');
        $this->addSql('SELECT setval(\'bris_porte_id_seq\', (SELECT MAX(id) FROM bris_porte))');
        $this->addSql('ALTER TABLE bris_porte ALTER id SET DEFAULT nextval(\'bris_porte_id_seq\')');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDBCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BC580EED4A93DAA5 ON bris_porte (requerant_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDBCC8DD14 ON bris_porte (liasse_documentaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE categorie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prejudice_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE prejudice (id SERIAL NOT NULL, requerant_id INT NOT NULL, liasse_documentaire_id INT NOT NULL, date_declaration DATE DEFAULT NULL, reference VARCHAR(20) DEFAULT NULL, note TEXT DEFAULT NULL, proposition_indemnisation NUMERIC(10, 2) DEFAULT NULL, motivation_proposition TEXT DEFAULT NULL, raccourci VARCHAR(20) DEFAULT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_39465c1fbcc8dd14 ON prejudice (liasse_documentaire_id)');
        $this->addSql('CREATE INDEX idx_39465c1f4a93daa5 ON prejudice (requerant_id)');
        $this->addSql('CREATE TABLE categorie (id SERIAL NOT NULL, code VARCHAR(50) NOT NULL, mnemo VARCHAR(50) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_497dd63477153098 ON categorie (code)');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT fk_39465c1fbcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT fk_39465c1f4a93daa5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4A93DAA5');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDBCC8DD14');
        $this->addSql('DROP INDEX IDX_BC580EED4A93DAA5');
        $this->addSql('DROP INDEX UNIQ_BC580EEDBCC8DD14');
        $this->addSql('ALTER TABLE bris_porte DROP requerant_id');
        $this->addSql('ALTER TABLE bris_porte DROP liasse_documentaire_id');
        $this->addSql('ALTER TABLE bris_porte DROP date_declaration');
        $this->addSql('ALTER TABLE bris_porte DROP reference');
        $this->addSql('ALTER TABLE bris_porte DROP note');
        $this->addSql('ALTER TABLE bris_porte DROP proposition_indemnisation');
        $this->addSql('ALTER TABLE bris_porte DROP motivation_proposition');
        $this->addSql('ALTER TABLE bris_porte DROP raccourci');
        $this->addSql('ALTER TABLE bris_porte ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eedbf396750 FOREIGN KEY (id) REFERENCES prejudice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
