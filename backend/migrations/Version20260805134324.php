<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260805134324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les entités Avocat/Barreau et la relation usagers.avocat_id (nullable, OneToOne)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avocats (numero_cnbf VARCHAR(6) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, raison_sociale VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, civilite VARCHAR(3) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, barreau_id VARCHAR(4) NOT NULL, PRIMARY KEY (numero_cnbf))');
        $this->addSql('CREATE INDEX IDX_946DF3E1D188F2AB ON avocats (barreau_id)');
        $this->addSql('CREATE TABLE barreaux (id VARCHAR(4) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE avocats ADD CONSTRAINT FK_946DF3E1D188F2AB FOREIGN KEY (barreau_id) REFERENCES barreaux (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE usagers ADD avocat_id VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE usagers ADD CONSTRAINT FK_3630FCE3EDBF2DB2 FOREIGN KEY (avocat_id) REFERENCES avocats (numero_cnbf) NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3630FCE3EDBF2DB2 ON usagers (avocat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avocats DROP CONSTRAINT FK_946DF3E1D188F2AB');
        $this->addSql('DROP TABLE avocats');
        $this->addSql('DROP TABLE barreaux');
        $this->addSql('ALTER TABLE usagers DROP CONSTRAINT FK_3630FCE3EDBF2DB2');
        $this->addSql('DROP INDEX UNIQ_3630FCE3EDBF2DB2');
        $this->addSql('ALTER TABLE usagers DROP avocat_id');
    }
}
