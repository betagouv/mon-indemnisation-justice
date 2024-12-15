<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213161031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajout du drapeau est_issu_attestation au test d'éligibiltié";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests ADD est_issu_attestation BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER date_soumission TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN eligibilite_tests.date_soumission IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C14A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_881877C14A93DAA5 ON eligibilite_tests (requerant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C14A93DAA5');
        $this->addSql('DROP INDEX UNIQ_881877C14A93DAA5');
        $this->addSql('ALTER TABLE eligibilite_tests DROP est_issu_attestation');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER date_soumission TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN eligibilite_tests.date_soumission IS NULL');
    }
}
