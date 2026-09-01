<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901084323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer les tables de validation de dossier & pièces jointes';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier_validation (id UUID NOT NULL, est_recevable BOOLEAN NOT NULL, commentaire VARCHAR(255) DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, dossier_id INT DEFAULT NULL, validateur_id INT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_38D61F4B611C0C56 ON dossier_validation (dossier_id)');
        $this->addSql('CREATE INDEX IDX_38D61F4BE57AEF2F ON dossier_validation (validateur_id)');
        $this->addSql('CREATE TABLE piece_jointe_validation (id UUID NOT NULL, est_recevable BOOLEAN NOT NULL, commentaire VARCHAR(255) NOT NULL, piece_jointe_id INT DEFAULT NULL, validation_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_90037416A3741A05 ON piece_jointe_validation (piece_jointe_id)');
        $this->addSql('CREATE INDEX IDX_90037416A2274850 ON piece_jointe_validation (validation_id)');
        $this->addSql('ALTER TABLE dossier_validation ADD CONSTRAINT FK_38D61F4B611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossiers (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE dossier_validation ADD CONSTRAINT FK_38D61F4BE57AEF2F FOREIGN KEY (validateur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE piece_jointe_validation ADD CONSTRAINT FK_90037416A3741A05 FOREIGN KEY (piece_jointe_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE piece_jointe_validation ADD CONSTRAINT FK_90037416A2274850 FOREIGN KEY (validation_id) REFERENCES dossier_validation (id)');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76e57aef2f');
        $this->addSql('DROP INDEX idx_d8698a76e57aef2f');
        $this->addSql('ALTER TABLE document DROP validateur_id');
        $this->addSql('ALTER TABLE document DROP est_valide');
        $this->addSql('ALTER TABLE document DROP date_validation');
        // Passer tous les dossiers actuellement à `A_INSTRUIRE` en `A_VERIFIER`
        $this->addSql(
            <<<SQL
UPDATE dossier_etats
SET etat = 'A_VERIFIER'
FROM (
    SELECT ed.id
    FROM dossiers d
        INNER JOIN dossier_etats ed ON d.etat_actuel_id = ed.id AND ed.etat = 'A_INSTRUIRE'
) ed
WHERE ed.id = dossier_etats.id;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier_validation DROP CONSTRAINT FK_38D61F4B611C0C56');
        $this->addSql('ALTER TABLE dossier_validation DROP CONSTRAINT FK_38D61F4BE57AEF2F');
        $this->addSql('ALTER TABLE piece_jointe_validation DROP CONSTRAINT FK_90037416A3741A05');
        $this->addSql('ALTER TABLE piece_jointe_validation DROP CONSTRAINT FK_90037416A2274850');
        $this->addSql('DROP TABLE dossier_validation');
        $this->addSql('DROP TABLE piece_jointe_validation');
        $this->addSql('ALTER TABLE document ADD validateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD est_valide BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD date_validation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76e57aef2f FOREIGN KEY (validateur_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d8698a76e57aef2f ON document (validateur_id)');
    }
}
