<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708210430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nettoyer les motifs rejets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
UPDATE dossier_etats
SET contexte = '{"motif_rejet": "MIS_EN_CAUSE"}'
WHERE
    etat = 'KO_A_SIGNER'
    and contexte->>'motifRejet' = 'est_vise'
SQL
        );
        $this->addSql(
            <<<SQL
UPDATE dossier_etats
SET contexte = '{"motif_rejet": "LOCATAIRE"}'
WHERE
    etat = 'KO_A_SIGNER'
    and contexte->>'motifRejet' = 'est_bailleur'
SQL
        );
        $this->addSql(
            <<<SQL
UPDATE dossier_etats
SET contexte = '{"motif_rejet": "LOCATAIRE_HEBERGEANT"}'
WHERE
    etat = 'KO_A_SIGNER'
    and contexte->>'motifRejet' = 'est_hebergeant'
SQL
        );
        $this->addSql(
            <<<SQL
UPDATE dossier_etats
SET contexte = '{"motif_rejet": null}'
WHERE
    etat = 'KO_A_SIGNER'
    and (
        contexte->>'motifRejet' = 'autre'
        or contexte is null
    )
SQL
        );
    }

    public function down(Schema $schema): void
    {
    }
}
