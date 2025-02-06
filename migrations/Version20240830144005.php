<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830144005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT fk_39465c1f4a93daa5');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf4a93daa5');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('CREATE TABLE requerants (id SERIAL NOT NULL, adresse_id INT DEFAULT NULL, personne_physique_id INT DEFAULT NULL, personne_morale_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles TEXT NOT NULL, password VARCHAR(255) NOT NULL, date_changement_mdp DATE DEFAULT NULL, est_verifie_courriel BOOLEAN NOT NULL, mnemo VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, grade VARCHAR(255) DEFAULT NULL, is_personne_morale BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C44DE7DC5C ON requerants (adresse_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C454472AC9 ON requerants (personne_physique_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_41CF50C435FE3BF6 ON requerants (personne_morale_id)');
        $this->addSql('ALTER INDEX UNIQ_IDENTIFIER_EMAIL RENAME TO UNIQ_USER_IDENTIFIER_EMAIL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON requerants (email)');
        $this->addSql('COMMENT ON COLUMN requerants.roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C44DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C454472AC9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants ADD CONSTRAINT FK_41CF50C435FE3BF6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d64935fe3bf6');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d6494de7dc5c');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d64954472ac9');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT FK_39465C1F4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT FK_39465C1F4A93DAA5');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF4A93DAA5');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, adresse_id INT DEFAULT NULL, personne_physique_id INT DEFAULT NULL, personne_morale_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, date_changement_mdp DATE DEFAULT NULL, is_verified BOOLEAN NOT NULL, mnemo VARCHAR(255) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, grade VARCHAR(255) DEFAULT NULL, is_personne_morale BOOLEAN DEFAULT false NOT NULL, active BOOLEAN DEFAULT true NOT NULL, roles TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_identifier_email ON "user" (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d64954472ac9 ON "user" (personne_physique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d6494de7dc5c ON "user" (adresse_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d64935fe3bf6 ON "user" (personne_morale_id)');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d64935fe3bf6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d6494de7dc5c FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d64954472ac9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C44DE7DC5C');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C454472AC9');
        $this->addSql('ALTER TABLE requerants DROP CONSTRAINT FK_41CF50C435FE3BF6');
        $this->addSql('DROP TABLE requerants');
        $this->addSql('ALTER TABLE prejudice DROP CONSTRAINT fk_39465c1f4a93daa5');
        $this->addSql('ALTER TABLE prejudice ADD CONSTRAINT fk_39465c1f4a93daa5 FOREIGN KEY (requerant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf4a93daa5');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bf4a93daa5 FOREIGN KEY (requerant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
