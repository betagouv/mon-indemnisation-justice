<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703150713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertir la champs document.size en entier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ALTER size DROP DEFAULT');
        $this->addSql('ALTER TABLE document ALTER size TYPE INT USING size::integer');
        // Déclarer les pièces jointes envoyées après le début de l'instruction comme émanant d'un agent
        $this->addSql(<<<SQL
update document
set est_ajout_requerant = false
from document doc
    inner join document_dossiers dd on doc.id = dd.document_id
    inner join (
        select d.id, ed.date as date_debut_instruction
        from bris_porte d
            left join dossier_etats ed on ed.dossier_id = d.id and ed.etat = 'EN_INSTRUCTION'
    ) d on dd.dossier_id = d.id
where
    doc.id = document.id
    and document.est_ajout_requerant is null
    and doc.date_ajout > d.date_debut_instruction;
SQL
        );
        // Supprimer les documents au format non supportés
        $this->addSql("delete from document where mime not in ('image/png', 'image/jpeg', 'image/webp', 'image/gif', 'application/pdf')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document ALTER size TYPE VARCHAR(255) USING size::varchar');
    }
}
