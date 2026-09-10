<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260910133740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Convertir les déclarations d'acceptation non signées en formulaire";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
UPDATE document
SET type = 'formulaire_acceptation'
FROM (
    -- Les documents de type 'courrier_requerant' associé à des dossiers non encore accepté
    SELECT doc.id AS document_id
    FROM document doc
        INNER JOIN document_dossiers dd ON doc.id = dd.document_id AND doc.type = 'courrier_requerant'
        INNER JOIN dossiers d ON dd.dossier_id = d.id
        INNER JOIN dossier_etats ed ON d.etat_actuel_id = ed.id
    WHERE
        ed.etat IN ('OK_A_SIGNER', 'OK_A_APPROUVER')
        AND NOT EXISTS (
            SELECT *
            FROM dossier_etats ed2
            WHERE
                ed2.dossier_id = d.id
                AND ed2.id <> ed.id
                AND ed2.etat = 'OK_A_VERIFIER'
        )
) d
WHERE d.document_id = document.id
SQL
        );

    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE document SET type = 'courrier_requerant' WHERE type= 'formulaire_acceptation'");

    }
}
