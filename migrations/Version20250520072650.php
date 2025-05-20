<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520072650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nettoyage du corps courrier pour les dossiers décidés';
    }

    public function up(Schema $schema): void
    {
        // Retirer la valeur de corps_courrier pour les dossiers dont le courrier de décision a été signé
        $this->addSql(<<<SQL
update bris_porte
set corps_courrier = null
from (
    select d.id as dossier_id
    from bris_porte d
        inner join dossier_etats ed on d.etat_actuel_id = ed.id and ed.etat in ('OK_A_APPROUVER', 'OK_A_VERIFIER', 'KO_REJETE')
     ) d
where bris_porte.id = d.dossier_id
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
