<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611083812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enrichir la table document';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ADD validateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD date_ajout TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE document ADD est_ajout_requerant BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD est_valide BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD date_validation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD corps_courrier TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD meta_donnees JSON DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN document.date_ajout IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN document.date_validation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76E57AEF2F FOREIGN KEY (validateur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D8698A76E57AEF2F ON document (validateur_id)');
        $this->addSql(<<<SQL
update document
set date_ajout = da.date_ajout,
    est_ajout_requerant = true
from (
    select doc.id as document_id, coalesce(de2.date, de1.date) as date_ajout
    from document doc
        inner join document_dossiers dd on doc.id = dd.document_id
        inner join bris_porte d on d.id = dd.dossier_id
        inner join dossier_etats de1 on de1.dossier_id = d.id and de1.etat = 'A_FINALISER'
        left join dossier_etats de2 on de2.dossier_id = d.id and de2.etat = 'A_INSTRUIRE'
) da
where da.document_id = document.id
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76E57AEF2F');
        $this->addSql('DROP INDEX IDX_D8698A76E57AEF2F');
        $this->addSql('ALTER TABLE document DROP validateur_id');
        $this->addSql('ALTER TABLE document DROP date_ajout');
        $this->addSql('ALTER TABLE document DROP est_ajout_requerant');
        $this->addSql('ALTER TABLE document DROP est_valide');
        $this->addSql('ALTER TABLE document DROP date_validation');
        $this->addSql('ALTER TABLE document DROP corps_courrier');
        $this->addSql('ALTER TABLE document DROP meta_donnees');
    }
}
