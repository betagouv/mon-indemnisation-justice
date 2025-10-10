<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007192057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réaligner les données json des documents et états liés à la décision';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed69c2e78d');
        $this->addSql('DROP INDEX idx_bc580eed69c2e78d');
        $this->addSql(
            <<<'SQL'
update document
set meta_donnees = dm.meta_donnees
from (select doc.id,
             
             (doc.meta_donnees::jsonb
                  -- Rapatrier le champ contexte.motifRefus à la racine en le renommant motifRejet 
                  || case
                         when doc.meta_donnees -> 'contexte' -> 'motifRefus' is not null then jsonb_build_object(
                                 'motifRejet', doc.meta_donnees -> 'contexte' -> 'motifRefus')
                         else '{}'::jsonb end
                 -- Rapatrier le champ contexte.montantIndemnisation à la racine
                 || case
                        when doc.meta_donnees -> 'contexte' -> 'montantIndemnisation' is not null
                            then jsonb_build_object('montantIndemnisation',
                                                    doc.meta_donnees -> 'contexte' -> 'montantIndemnisation')
                        else '{}'::jsonb end)
                 -- Supprimer le champ contexte 
                 - 'contexte' as meta_donnees
      from document doc
      where doc.type = 'courrier_ministere'
        and doc.meta_donnees is not null
) dm
where
    document.id = dm.id
SQL
        );
        $this->addSql(
            <<<'SQL'
update dossier_etats
set contexte = ed.contexte
from (
    select
        ed.id,
        (  
          (ed.contexte::jsonb
               -- Renommer le champ `motif` en `motifRejet`
               || case
                      when ed.contexte -> 'motif' is not null
                          then jsonb_build_object('motifRejet', ed.contexte -> 'motif')
                      else '{}'::jsonb end
              -- Renommer le champ `montant` en `montantIndemnisation`
              || case
                     when ed.contexte -> 'montant' is not null
                         then jsonb_build_object('montantIndemnisation', ed.contexte -> 'montant')
                     else '{}'::jsonb end
              ) - 'montant' - 'motif')::json
            as contexte
      from dossier_etats ed
      where ed.etat in ('OK_A_SIGNER', 'KO_A_SIGNER')
) ed
where ed.id = dossier_etats.id
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed69c2e78d FOREIGN KEY (type_institution_securite_publique) REFERENCES institutions_securite_publique (type) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_bc580eed69c2e78d ON bris_porte (type_institution_securite_publique)');
    }
}
