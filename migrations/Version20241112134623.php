<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112134623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Garder l'état courant du dossier dans la table";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD etat_actuel_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDF90DA413 FOREIGN KEY (etat_actuel_id) REFERENCES dossier_etats (id) ON DELETE CASCADE');
        $this->addSql(<<<SQL
update bris_porte
set etat_actuel_id = ec.id
from (
    select distinct on (dossier_id) dossier_id, id from dossier_etats
    order by dossier_id, date desc
) ec
where
    etat_actuel_id is null
    and ec.dossier_id = bris_porte.id
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC580EEDF90DA413 ON bris_porte (etat_actuel_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDF90DA413');
        $this->addSql('DROP INDEX UNIQ_BC580EEDF90DA413');
        $this->addSql('ALTER TABLE bris_porte DROP etat_actuel_id');
    }
}
