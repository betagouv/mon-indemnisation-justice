<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023094706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter la colonne date_depot à la table bris_porte';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD date_depot TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql(
            <<<'SQL'
update bris_porte
set date_depot = ed.date
from (
    select ed.dossier_id, ed.date::date
    from dossier_etats ed
    where ed.etat = 'A_ATTRIBUER'
) ed
where id = ed.dossier_id
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP date_depot');
    }
}
