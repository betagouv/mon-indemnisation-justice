<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304144147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Rectifier les valeurs de rapport_au_logement pour les tests d'éligibilité";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            <<<SQL
update eligibilite_tests
set rapport_au_logement = case
    when rapport_au_logement = 'LOC' then 'LOCATAIRE'
    when rapport_au_logement = 'PRO' then 'PROPRIETAIRE'
    when rapport_au_logement = 'BAI' then 'BAILLEUR'
    when rapport_au_logement = 'AUT' then 'AUTRE'
    end
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
update eligibilite_tests
set rapport_au_logement = case
    when rapport_au_logement = 'LOCATAIRE' then 'LOC'
    when rapport_au_logement = 'PROPRIETAIRE' then 'PRO'
    when rapport_au_logement = 'BAILLEUR' then 'BAI'
    when rapport_au_logement = 'AUTRE' then 'AUT'
    end
SQL
        );
    }
}
