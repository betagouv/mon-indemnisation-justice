<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909134344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD description_requerant TEXT DEFAULT NULL');
        $this->addSql(
            <<<'SQL'
update bris_porte
set description_requerant = te.description
from (
    select bp.id as dossier_id, te.description from eligibilite_tests te 
        inner join bris_porte bp on te.id = bp.test_eligibilite_id) te
where id = te.dossier_id
SQL
        );
        $this->addSql('ALTER TABLE eligibilite_tests DROP description');
        $this->addSql("update bris_porte set qualite_requerant = 'BAI' where qualite_requerant = 'HEB'");
        $this->addSql('ALTER TABLE eligibilite_tests ADD rapport_au_logement VARCHAR(255) DEFAULT NULL');
        $this->addSql("update eligibilite_tests set rapport_au_logement = case when est_proprietaire then 'PRO' else 'LOC' end");
        $this->addSql('ALTER TABLE eligibilite_tests DROP est_proprietaire');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests ADD est_proprietaire BOOLEAN DEFAULT NULL');
        $this->addSql("update eligibilite_tests set est_proprietaire =  case when rapport_au_logement = 'PRO' then true else false end");
        $this->addSql('ALTER TABLE eligibilite_tests DROP rapport_au_logement');
        $this->addSql("update bris_porte set qualite_requerant = 'HEB' where qualite_requerant = 'BAI'");
        $this->addSql('ALTER TABLE eligibilite_tests ADD description TEXT DEFAULT NULL');
        $this->addSql(
            <<<'SQL'
update eligibilite_tests
set description = d.description_requerant
from (
    select id, description_requerant, test_eligibilite_id from bris_porte
) d
where eligibilite_tests.id = d.test_eligibilite_id
SQL
        );
        $this->addSql('ALTER TABLE bris_porte DROP description_requerant');
    }
}
