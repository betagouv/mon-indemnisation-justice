<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624134202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Transfert des données courrier et corps courrier vers un document en brouillon';
    }

    public function up(Schema $schema): void
    {
        // Changer le type des documents `preuve_paiement_facture` pour `facture`:
        $this->addSql("update document set type = 'facture' where type = 'preuve_paiement_facture'");

        // Colonne temporaire `document.dossier_id`
        $this->addSql('ALTER TABLE document ADD COLUMN dossier_id integer');

        // Création du document de type `courrier_ministere` pour les dossiers avant signature du courrier
        $this->addSql(<<<SQL
with docs as (
    insert into document (corps_courrier, type, filename, date_ajout, mime, est_ajout_requerant, dossier_id)
    select d.corps_courrier, 'courrier_ministere' as type , dc.filename, dc.date_creation as date_ajout, 'application/pdf', false, d.id as dossier_id 
    from bris_porte d
        inner join public.dossier_etats de on de.id = d.etat_actuel_id and de.etat in ('EN_INSTRUCTION', 'OK_A_SIGNER', 'KO_A_SIGNER')
        inner join dossier_courriers dc on d.courrier_actuel_id = dc.id
    where not exists (
        select dd.*
            from document_dossiers dd
        inner join document doc on dd.document_id = doc.id and doc.type = 'courrier_ministere'
        where dd.dossier_id = d.id
    )
    returning id as document_id, dossier_id
)
insert into document_dossiers (dossier_id, document_id)
select docs.dossier_id, docs.document_id
from docs
SQL
        );
        // Création du document de type `arrete_paiement` pour les dossiers avant signature de l'arrêté de paiement
        $this->addSql(<<<SQL
with docs as (
    insert into document (corps_courrier, type, filename, date_ajout, mime, est_ajout_requerant, dossier_id)
        select d.corps_courrier, 'arrete_paiement', dc.filename, dc.date_creation as date_ajout, 'application/pdf', false, d.id as dossier_id
        from bris_porte d
                inner join public.dossier_etats de on de.id = d.etat_actuel_id and de.etat = 'OK_A_VERIFIER'
                inner join dossier_courriers dc on d.courrier_actuel_id = dc.id
        where not exists (
            select dd.*
                from document_dossiers dd
            inner join document doc on dd.document_id = doc.id and doc.type = 'arrete_paiement'
            where dd.dossier_id = d.id
        )
    returning id as document_id, dossier_id
)
insert into document_dossiers (dossier_id, document_id)
select docs.dossier_id, docs.document_id
from docs
SQL
        );
        $this->addSql('ALTER TABLE document DROP COLUMN dossier_id');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eede785c776');
        $this->addSql('DROP INDEX uniq_bc580eede785c776');
        $this->addSql('ALTER TABLE bris_porte DROP courrier_actuel_id');
        $this->addSql('ALTER TABLE bris_porte DROP corps_courrier');
        $this->addSql('ALTER TABLE document RENAME COLUMN corps_courrier TO corps');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document RENAME COLUMN corps TO corps_courrier');
        $this->addSql('ALTER TABLE bris_porte ADD courrier_actuel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD corps_courrier TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eede785c776 FOREIGN KEY (courrier_actuel_id) REFERENCES dossier_courriers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_bc580eede785c776 ON bris_porte (courrier_actuel_id)');
    }
}
