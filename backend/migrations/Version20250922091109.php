<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922091109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne bris_porte.type_attestation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse ADD CONSTRAINT FK_C35F0816E5127261 FOREIGN KEY (commune_code) REFERENCES geo_communes (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C35F0816E5127261 ON adresse (commune_code)');
        $this->addSql('ALTER TABLE bris_porte ADD type_attestation VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER rapport_au_logement TYPE VARCHAR(3)');
        $this->addSql(
            <<<'SQL'
update document
set meta_donnees = dm.meta_donnees
from (
    select
        doc.id,
        (
            doc.meta_donnees::jsonb - 'estAttestation' || ('{"typeAttestation":' || case when coalesce((doc.meta_donnees ->> 'estAttestation')::boolean, false) then '"NOUVELLE_ATTESTATION"' else 'null' end || '}')::jsonb)::json as meta_donnees
      from document doc
      where
          doc.type = 'attestation_information'
            and doc.meta_donnees is not null
      ) dm
where
    document.id = dm.id
SQL
        );
        $this->addSql(
            <<<'SQL'
update bris_porte
set type_attestation = doc.type_attestation
from (
    select distinct on (dd.dossier_id) dd.dossier_id, doc.meta_donnees->>'typeAttestation' as type_attestation
    from document_dossiers dd
        left join document doc on doc.id = dd.document_id
    where
        doc.type = 'attestation_information'
        and doc.meta_donnees is not null
    order by dd.dossier_id, type_attestation nulls last
    ) doc
where
    doc.dossier_id = bris_porte.id
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse DROP CONSTRAINT FK_C35F0816E5127261');
        $this->addSql('DROP INDEX IDX_C35F0816E5127261');
        $this->addSql('ALTER TABLE bris_porte DROP type_attestation');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER rapport_au_logement DROP NOT NULL');
        $this->addSql('ALTER TABLE eligibilite_tests ALTER rapport_au_logement TYPE VARCHAR(255)');
    }
}
