<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029135607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer les tables declarations_erreur_operationnelle et procedures_judiciaires';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE declarations_erreur_operationnelle (id UUID NOT NULL, adresse_id INT NOT NULL, procedure_id UUID NOT NULL, agent_id INT DEFAULT NULL, reference VARCHAR(6) NOT NULL, date_operation DATE NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_soumission TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, infos_requerant JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AFEDB0224DE7DC5C ON declarations_erreur_operationnelle (adresse_id)');
        $this->addSql('CREATE INDEX IDX_AFEDB0221624BCD2 ON declarations_erreur_operationnelle (procedure_id)');
        $this->addSql('CREATE INDEX declaration_erreur_operationnelle_agent_idx ON declarations_erreur_operationnelle (agent_id)');
        $this->addSql('CREATE UNIQUE INDEX declaration_erreur_operationnelle_reference_idx ON declarations_erreur_operationnelle (reference)');
        $this->addSql('COMMENT ON COLUMN declarations_erreur_operationnelle.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN declarations_erreur_operationnelle.procedure_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN declarations_erreur_operationnelle.date_operation IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN declarations_erreur_operationnelle.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN declarations_erreur_operationnelle.date_soumission IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE procedures_judiciaires (id UUID NOT NULL, numero_procedure VARCHAR(255) NOT NULL, service_enqueteur VARCHAR(255) NOT NULL, juridiction_ou_parquet VARCHAR(255) DEFAULT NULL, nom_magistrat VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN procedures_judiciaires.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT FK_AFEDB0224DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT FK_AFEDB0221624BCD2 FOREIGN KEY (procedure_id) REFERENCES procedures_judiciaires (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT FK_AFEDB0223414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE agents ADD telephone VARCHAR(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD declaration_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN bris_porte.declaration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_erreur_operationnelle (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDC06258A3 ON bris_porte (declaration_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDC06258A3');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT FK_AFEDB0224DE7DC5C');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT FK_AFEDB0221624BCD2');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT FK_AFEDB0223414710B');
        $this->addSql('DROP TABLE declarations_erreur_operationnelle');
        $this->addSql('DROP TABLE procedures_judiciaires');
        $this->addSql('DROP INDEX UNIQ_BC580EEDC06258A3');
        $this->addSql('ALTER TABLE bris_porte DROP declaration_id');
        $this->addSql('ALTER TABLE agents DROP telephone');
    }
}
