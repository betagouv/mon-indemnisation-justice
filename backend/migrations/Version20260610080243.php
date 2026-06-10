<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use MonIndemnisationJustice\Entity\AdministrationType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610080243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Déclarer le Ministère de l'Intérieur comme administration";
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
INSERT INTO administrations (code, siret, date_integration)
VALUES ('MI', :siret_mi, null)
ON CONFLICT (code) DO UPDATE SET siret = :siret_mi
SQL
            ,
            ['siret_mi' => AdministrationType::SIRET_MI]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM administrations WHERE code = :siret_mi',
            ['siret_mi' => AdministrationType::SIRET_MI]
        );
    }
}
