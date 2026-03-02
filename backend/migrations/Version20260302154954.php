<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302154954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déplacer les données personnes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers ALTER bris_porte_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN dossiers.bris_porte_id IS \'(DC2Type:uuid)\'');

        $this->addSql('ALTER TABLE personne_physique ADD adresse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT FK_5C2B29A24DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(
            <<<SQL
UPDATE personne_physique
SET adresse_id = u.adresse_id
FROM (
    SELECT u.adresse_id, pp.id as personne_physique_id
    FROM usagers u
        INNER JOIN personnes p ON u.personne_id = p.id
        INNER JOIN personne_physique pp ON pp.personne_id = p.id
) u
WHERE personne_physique.id = u.personne_physique_id
SQL
        );

        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C2B29A24DE7DC5C ON personne_physique (adresse_id)');
        $this->addSql('ALTER TABLE usagers DROP CONSTRAINT fk_41cf50c44de7dc5c');
        $this->addSql('DROP INDEX uniq_3630fce34de7dc5c');
        $this->addSql('ALTER TABLE usagers DROP adresse_id');
        $this->addSql('ALTER TABLE personne_physique RENAME TO personnes_physiques');
        $this->addSql('ALTER TABLE personne_morale RENAME TO personnes_morales');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_physiques RENAME TO personne_physique');
        $this->addSql('ALTER TABLE personnes_morales RENAME TO personne_morale');
        $this->addSql('ALTER TABLE dossiers ALTER bris_porte_id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN dossiers.bris_porte_id IS NULL');

        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT FK_5C2B29A24DE7DC5C');
        $this->addSql('DROP INDEX UNIQ_5C2B29A24DE7DC5C');
        $this->addSql('ALTER TABLE usagers ADD adresse_id INT DEFAULT NULL');
        $this->addSql(
            <<<SQL
UPDATE usagers
SET adresse_id = pp.adresse_id
FROM (
    SELECT pp.adresse_id, u.id as usager_id
    FROM personne_physique pp
        INNER JOIN personnes p ON pp.personne_id = p.id
        INNER JOIN usagers u ON u.personne_id = p.id
) pp
WHERE usagers.id = pp.usager_id
SQL
        );

        $this->addSql('ALTER TABLE personne_physique DROP adresse_id');
        $this->addSql('ALTER TABLE usagers ADD CONSTRAINT fk_41cf50c44de7dc5c FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_3630fce34de7dc5c ON usagers (adresse_id)');
    }
}
