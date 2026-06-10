<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610131925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommer la table eligibilite_tests en bris_porte_test_eligibilite pour préparer la distinction avec celui des dysfonctionnements';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests RENAME TO bris_porte_test_eligibilite');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bris_porte_test_eligibilite RENAME TO eligibilite_tests');
    }
}
