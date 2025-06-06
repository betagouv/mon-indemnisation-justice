<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606123109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Remplacer la date de changement de mot de passe par la ate d'inscription";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE requerants ADD date_inscription TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT '2024-01-01' NOT NULL");
        $this->addSql(<<<SQL
update requerants
set date_inscription = ri.date_premier_dossier
from (
    select distinct on (r.id) r.id as requerant_id, ed.date as date_premier_dossier
    from requerants r
        inner join bris_porte d on d.requerant_id = r.id
        inner join dossier_etats ed on ed.dossier_id = d.id
    order by r.id, ed.date
) ri
where id = ri.requerant_id;
SQL);
        $this->addSql('ALTER TABLE requerants ALTER date_inscription DROP DEFAULT');
        $this->addSql('ALTER TABLE requerants DROP date_changement_mdp');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE requerants ADD date_changement_mdp DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE requerants DROP date_inscription');
    }
}
