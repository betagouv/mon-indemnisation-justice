<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217160042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restructurer les données de déclaration de bris de porte FDO';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD precisions_requerant TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE procedures_judiciaires ADD telephone VARCHAR(20)');
        $this->addSql(
            <<<'SQL'
update procedures_judiciaires
set telephone = d.telephone
from declarations_fdo_bris_porte d
where d.procedure_id = procedures_judiciaires.id
SQL
        );
        $this->addSql('ALTER TABLE procedures_judiciaires ALTER COLUMN telephone SET NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP telephone');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP precisions_requerant');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD telephone VARCHAR(20) NOT NULL');
        $this->addSql(
            <<<'SQL'
update declarations_fdo_bris_porte
set telephone = p.telephone
from procedures_judiciaires p
where procedure_id = p.id
SQL
        );
        $this->addSql('ALTER TABLE procedures_judiciaires DROP telephone');
    }
}
