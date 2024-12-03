<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203102655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Déplacement des données de test d'éligibilité dans une table dédiée";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE eligibilite_tests (id SERIAL NOT NULL, departement_code VARCHAR(3) NOT NULL, est_vise BOOLEAN DEFAULT NULL, est_hebergeant BOOLEAN DEFAULT NULL, est_proprietaire BOOLEAN DEFAULT NULL, a_contacte_assurance BOOLEAN DEFAULT NULL, a_contacte_bailleur BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_881877C16A333750 ON eligibilite_tests (departement_code)');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C16A333750 FOREIGN KEY (departement_code) REFERENCES geo_departements (code) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD test_eligibilite_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED9450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EED9450CE1E ON bris_porte (test_eligibilite_id)');
        // Transfert des données
        $this->addSql('ALTER TABLE eligibilite_tests ADD COLUMN source_dossier_id INT DEFAULT NULL');
        $this->addSql(<<<SQL
INSERT INTO eligibilite_tests (source_dossier_id, departement_code, est_vise, est_hebergeant, est_proprietaire, a_contacte_assurance, a_contacte_bailleur)
SELECT d.id, d.departement_code, d.est_vise, d.est_hebergeant, d.est_proprietaire, d.a_contact_assurance, d.a_contact_bailleur
FROM bris_porte d
WHERE d.departement_code IS NOT NULL
SQL
        );
        $this->addSql(<<<SQL
UPDATE bris_porte
SET test_eligibilite_id = et.id
FROM (
    SELECT et.id, et.source_dossier_id
    FROM eligibilite_tests et
) et
WHERE et.source_dossier_id = bris_porte.id
SQL
        );
        $this->addSql('ALTER TABLE eligibilite_tests DROP COLUMN source_dossier_id');
        $this->addSql('ALTER TABLE requerants DROP test_eligibilite');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C16A333750');
        $this->addSql('DROP TABLE eligibilite_tests');
        $this->addSql('ALTER TABLE requerants ADD test_eligibilite JSON DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE bris_porte DROP test_eligibilite_id');
    }
}
