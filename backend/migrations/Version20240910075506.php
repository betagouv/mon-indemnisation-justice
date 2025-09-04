<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910075506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne test_eligibilite à requerants pour stocker le test d\'éligbilité';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE requerants ADD test_eligibilite JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE requerants DROP test_eligibilite');
    }
}
