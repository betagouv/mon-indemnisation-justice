<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302101540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Associer personnes morales ou physiques directement au dossier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers ADD requerant_personne_physique_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers ADD requerant_personne_morale_id INT DEFAULT NULL');
        // Déplacer personne morale vers le dossier
        $this->addSql(
            <<<SQL
UPDATE dossiers
SET requerant_personne_morale_id = pm.id
FROM (
    SELECT pm.id, d.id as dossier_id
    FROM personne_morale pm
        INNER JOIN usagers u ON u.personne_morale_id = pm.id
        INNER JOIN dossiers d ON d.usager_id = u.id
) pm
WHERE dossiers.id = pm.dossier_id
SQL
        );
        // Déplacer personne physique vers le dossier
        $this->addSql(
            <<<SQL
UPDATE dossiers
SET requerant_personne_physique_id = pp.id
FROM (
    SELECT pp.id, d.id as dossier_id FROM personne_physique pp
        INNER JOIN usagers u ON u.personne_physique_id = pp.id
        INNER JOIN dossiers d ON d.usager_id = u.id
) pp
WHERE dossiers.id = pp.dossier_id AND requerant_personne_morale_id IS NULL
SQL
        );

        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E4825342A3 FOREIGN KEY (requerant_personne_physique_id) REFERENCES personne_physique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E429833C4A FOREIGN KEY (requerant_personne_morale_id) REFERENCES personne_morale (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A38E22E4825342A3 ON dossiers (requerant_personne_physique_id)');
        $this->addSql('CREATE INDEX IDX_A38E22E429833C4A ON dossiers (requerant_personne_morale_id)');
        $this->addSql('ALTER TABLE personne_morale ADD representant_legal_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN personne_morale.representant_legal_id IS \'(DC2Type:uuid)\'');
        // Relier la personne de la personne physique à la personne morale en qualité de représentant légal
        $this->addSql(
            <<<SQL
UPDATE personne_morale
SET representant_legal_id = p.id
FROM (
    SELECT p.id, pm.id as personne_morale_id
    FROM personnes p
        INNER JOIN personne_physique pp ON pp.personne_id = p.id
        INNER JOIN usagers u ON u.personne_physique_id = pp.id
        INNER JOIN personne_morale pm ON u.personne_morale_id = pm.id
) p
WHERE personne_morale.id = p.personne_morale_id
SQL
        );

        $this->addSql('ALTER TABLE personne_morale ADD CONSTRAINT FK_56031D2AE4239E5A FOREIGN KEY (representant_legal_id) REFERENCES personnes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_56031D2AE4239E5A ON personne_morale (representant_legal_id)');

        $this->addSql('ALTER TABLE usagers ADD personne_id UUID');
        $this->addSql('COMMENT ON COLUMN usagers.personne_id IS \'(DC2Type:uuid)\'');
        // Lier l'usager à sa personne et non plus sa personne physique
        $this->addSql(
            <<<SQL

UPDATE usagers
SET personne_id = p.id
FROM (
    SELECT p.id, u.id as usager_id
    FROM personnes p
        INNER JOIN personne_physique pp ON pp.personne_id = p.id
        INNER JOIN usagers u ON u.personne_physique_id = pp.id
) p
WHERE usagers.id = p.usager_id
SQL
        );

        $this->addSql('ALTER TABLE usagers ALTER COLUMN personne_id SET NOT NULL');

        $this->addSql('ALTER TABLE usagers DROP CONSTRAINT fk_41cf50c435fe3bf6');
        $this->addSql('ALTER TABLE usagers DROP CONSTRAINT fk_41cf50c454472ac9');
        $this->addSql('DROP INDEX uniq_3630fce335fe3bf6');
        $this->addSql('DROP INDEX uniq_3630fce354472ac9');

        $this->addSql('ALTER TABLE usagers DROP personne_physique_id');
        $this->addSql('ALTER TABLE usagers DROP personne_morale_id');


        $this->addSql('ALTER TABLE usagers ADD CONSTRAINT FK_3630FCE3A21BD112 FOREIGN KEY (personne_id) REFERENCES personnes (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3630FCE3A21BD112 ON usagers (personne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E4825342A3');
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E429833C4A');
        $this->addSql('DROP INDEX IDX_A38E22E4825342A3');
        $this->addSql('DROP INDEX IDX_A38E22E429833C4A');
        $this->addSql('ALTER TABLE dossiers DROP requerant_personne_physique_id');
        $this->addSql('ALTER TABLE dossiers DROP requerant_personne_morale_id');
        $this->addSql('ALTER TABLE personne_morale DROP CONSTRAINT FK_56031D2AE4239E5A');
        $this->addSql('DROP INDEX UNIQ_56031D2AE4239E5A');
        $this->addSql('ALTER TABLE personne_morale DROP representant_legal_id');
        $this->addSql('ALTER TABLE usagers DROP CONSTRAINT FK_3630FCE3A21BD112');
        $this->addSql('DROP INDEX UNIQ_3630FCE3A21BD112');
        $this->addSql('ALTER TABLE usagers ADD personne_physique_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE usagers ADD personne_morale_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE usagers DROP personne_id');
        $this->addSql('ALTER TABLE usagers ADD CONSTRAINT fk_41cf50c435fe3bf6 FOREIGN KEY (personne_morale_id) REFERENCES personne_morale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE usagers ADD CONSTRAINT fk_41cf50c454472ac9 FOREIGN KEY (personne_physique_id) REFERENCES personne_physique (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_3630fce335fe3bf6 ON usagers (personne_morale_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_3630fce354472ac9 ON usagers (personne_physique_id)');
    }
}
