<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250827150654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Basculer les dossiers sans rédacteur vers l'état 'A_ATTRIBUER'";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP est_lie_attestation');
        $this->addSql(<<<SQL
update
    dossier_etats
set etat = 'A_ATTRIBUER'
from (
    select ed.id
    from bris_porte d
    inner join dossier_etats ed on d.etat_actuel_id = ed.id
    where ed.etat = 'A_INSTRUIRE' and d.redacteur_id is null
) ed
where dossier_etats.id = ed.id
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD est_lie_attestation BOOLEAN DEFAULT false NOT NULL');
    }
}
