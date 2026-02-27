<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227193422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la table personnes et séparer les données de personnes physiques';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personnes (id UUID NOT NULL, civilite VARCHAR(3) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, nom_naissance VARCHAR(255) DEFAULT NULL, courriel VARCHAR(255) DEFAULT NULL, telephone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN personnes.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE personne_physique ADD personne_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN personne_physique.personne_id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE personnes ADD COLUMN personne_physique_id integer');
        // Rapatrier les données de `personne_physique` vers `personne`
        $this->addSql(
            <<<SQL
WITH p AS (
    INSERT INTO personnes (id, personne_physique_id, civilite, prenom, nom, nom_naissance, courriel, telephone)
    SELECT gen_random_uuid(), pp.id as personne_physique_id, pp.civilite, pp.prenom1, pp.nom, pp.nom_naissance, pp.email, pp.telephone
    FROM personne_physique pp
    RETURNING id, personne_physique_id
)
UPDATE personne_physique
SET personne_id = p.id
FROM p
WHERE personne_physique.id = p.personne_physique_id;
SQL
        );

        $this->addSql('ALTER TABLE personnes DROP COLUMN personne_physique_id');
        $this->addSql('ALTER TABLE personne_physique DROP nom');
        $this->addSql('ALTER TABLE personne_physique DROP prenom1');
        $this->addSql('ALTER TABLE personne_physique DROP telephone');
        $this->addSql('ALTER TABLE personne_physique DROP nom_naissance');
        $this->addSql('ALTER TABLE personne_physique DROP email');
        $this->addSql('ALTER TABLE personne_physique DROP civilite');

        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A2A21BD112 FOREIGN KEY (personne_id) REFERENCES personnes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C2B29A2A21BD112 ON personne_physique (personne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A2A21BD112');
        $this->addSql('DROP TABLE personnes');
        $this->addSql('DROP INDEX UNIQ_5C2B29A2A21BD112');
        $this->addSql('ALTER TABLE personne_physique ADD nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD prenom1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD telephone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD nom_naissance VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD civilite VARCHAR(3) DEFAULT NULL');
        $this->addSql(
            <<<SQL
WITH p AS (
    SELECT pp.id as personne_physique_id, civilite, prenom, nom, nom_naissance, courriel, telephone
    FROM personnes p
        INNER JOIN personne_physique pp on pp.personne_id = p.id
)
UPDATE personne_physique
SET personne_physique.nom = p.nom,
    personne_physique.prenom1 = p.prenom,
    personne_physique.telephone = p.telephone,
    personne_physique.nom_naissance = p.nom_naissance,
    personne_physique.email = p.courriel,
    personne_physique.civilite = p.civilite
WHERE personne_physique.id = p.personne_physique_id
SQL
        );

        $this->addSql('ALTER TABLE personne_physique DROP personne_id');
    }
}
