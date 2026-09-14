<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914155046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Affiner le type du champ dysfonctionnement_test_eligibilite.preuves_diligences';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE dysfonctionnement_test_eligibilite ALTER preuves_diligences TYPE VARCHAR(20) USING CASE WHEN preuves_diligences = TRUE THEN 'avec_justificatifs' ELSE 'pas_de_demarche' END");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE dysfonctionnement_test_eligibilite ALTER preuves_diligences TYPE BOOLEAN USING CASE WHEN preuves_diligences = 'pas_de_demarche' THEN FALSE ELSE TRUE END");
    }
}
