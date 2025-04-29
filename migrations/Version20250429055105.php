<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429055105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
update bris_porte
set corps_courrier = '<p>
    J’accuse réception de votre requête par laquelle vous
    sollicitez' || case when d.qualite_requerant is not null then (', en qualité de ' || d.qualite_requerant || ', ') else '' end ||
    ' l''indemnisation du préjudice subi en raison de la détérioration de la porte d’entrée du logement sis
    ' || d.adresse || '. Cette dégradation aurait été occasionnée au cours d’une opération de police judiciaire menée le
    ' || date_operation || '</p>' || bris_porte.corps_courrier
from (
    select
        d.id as dossier_id,
        case
            when d.qualite_requerant = 'PRO' then 'propriétaire'
            when d.qualite_requerant = 'LOC' then 'locataire'
            when d.qualite_requerant = 'HEB' then 'hébergeant'
            else d.precision_requerant
        end as qualite_requerant,
        a.ligne1 ||' '|| coalesce(a.ligne2, '') || ' à ' || a.localite || ' (' || substr(a.code_postal, 1, 2) || ')' as adresse,
         to_char(d.date_operation_pj, 'DD')::int || ' ' || case
             when to_char(d.date_operation_pj, 'MM') = '01' then 'janvier'
             when to_char(d.date_operation_pj, 'MM') = '02' then 'février'
             when to_char(d.date_operation_pj, 'MM') = '03' then 'mars'
             when to_char(d.date_operation_pj, 'MM') = '04' then 'avril'
             when to_char(d.date_operation_pj, 'MM') = '05' then 'mai'
             when to_char(d.date_operation_pj, 'MM') = '06' then 'juin'
             when to_char(d.date_operation_pj, 'MM') = '07' then 'juillet'
             when to_char(d.date_operation_pj, 'MM') = '08' then 'août'
             when to_char(d.date_operation_pj, 'MM') = '09' then 'septembre'
             when to_char(d.date_operation_pj, 'MM') = '10' then 'octobre'
             when to_char(d.date_operation_pj, 'MM') = '11' then 'novembre'
             else 'décembre' end
             || ' ' || to_char(d.date_operation_pj, 'YYYY') as date_operation
    from bris_porte d
        inner join adresse a on d.adresse_id = a.id
) d
where
    bris_porte.id = d.dossier_id
    and corps_courrier is not null
    and corps_courrier not like '%<p>J’accuse réception%'
SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
