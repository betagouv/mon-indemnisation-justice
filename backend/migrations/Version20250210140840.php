<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210140840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remplacement de la liasse documentaire par un many to many entre dossiers et documents';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne_morale DROP CONSTRAINT fk_56031d2abcc8dd14');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eedbcc8dd14');
        $this->addSql('ALTER TABLE personne_physique DROP CONSTRAINT fk_5c2b29a2bcc8dd14');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76bcc8dd14');
        $this->addSql('CREATE TABLE document_dossiers (dossier_id INT NOT NULL, document_id INT NOT NULL, PRIMARY KEY(dossier_id, document_id))');
        $this->addSql('CREATE INDEX IDX_2C4FD3BD3C487237 ON document_dossiers (dossier_id)');
        $this->addSql('CREATE INDEX IDX_2C4FD3BDC33F7837 ON document_dossiers (document_id)');
        $this->addSql('ALTER TABLE document_dossiers ADD CONSTRAINT FK_2C4FD3BD3C487237 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document_dossiers ADD CONSTRAINT FK_2C4FD3BDC33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql(<<<SQL
insert into document_dossiers (dossier_id, document_id)
select dos.id as dossier_id, doc.id as document_id
from bris_porte dos
    inner join document doc on dos.liasse_documentaire_id = doc.liasse_documentaire_id
union all
select dos.id as dossier_id, doc.id as document_id
from personne_physique pp
    inner join requerants r on r.personne_physique_id = pp.id
    inner join bris_porte dos on dos.requerant_id = r.id
    inner join document doc on pp.liasse_documentaire_id = doc.liasse_documentaire_id
union all
select dos.id as dossier_id, doc.id as document_id
from personne_morale pm
    inner join requerants r on r.personne_physique_id = pm.id
    inner join bris_porte dos on dos.requerant_id = r.id
    inner join document doc on pm.liasse_documentaire_id = doc.liasse_documentaire_id
SQL);
        $this->addSql('DROP SEQUENCE liasse_documentaire_id_seq CASCADE');
        $this->addSql('DROP TABLE liasse_documentaire');
        $this->addSql('DROP INDEX uniq_bc580eedbcc8dd14');
        $this->addSql('ALTER TABLE bris_porte DROP liasse_documentaire_id');
        $this->addSql('DROP INDEX idx_d8698a76bcc8dd14');
        $this->addSql('DROP INDEX document_liasse_documentaire_id_type_idx');
        $this->addSql('ALTER TABLE document DROP liasse_documentaire_id');
        $this->addSql('DROP INDEX uniq_56031d2abcc8dd14');
        $this->addSql('ALTER TABLE personne_morale DROP liasse_documentaire_id');
        $this->addSql('DROP INDEX uniq_5c2b29a2bcc8dd14');
        $this->addSql('ALTER TABLE personne_physique DROP liasse_documentaire_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE liasse_documentaire_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE liasse_documentaire (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE document_dossiers DROP CONSTRAINT FK_2C4FD3BD3C487237');
        $this->addSql('ALTER TABLE document_dossiers DROP CONSTRAINT FK_2C4FD3BDC33F7837');
        $this->addSql('DROP TABLE document_dossiers');
        $this->addSql('ALTER TABLE personne_morale ADD liasse_documentaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE personne_morale ADD CONSTRAINT fk_56031d2abcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_56031d2abcc8dd14 ON personne_morale (liasse_documentaire_id)');
        $this->addSql('ALTER TABLE bris_porte ADD liasse_documentaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eedbcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_bc580eedbcc8dd14 ON bris_porte (liasse_documentaire_id)');
        $this->addSql('ALTER TABLE personne_physique ADD liasse_documentaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE personne_physique ADD CONSTRAINT fk_5c2b29a2bcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5c2b29a2bcc8dd14 ON personne_physique (liasse_documentaire_id)');
        $this->addSql('ALTER TABLE document ADD liasse_documentaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76bcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d8698a76bcc8dd14 ON document (liasse_documentaire_id)');
        $this->addSql('CREATE INDEX document_liasse_documentaire_id_type_idx ON document (liasse_documentaire_id, type)');
    }
}
