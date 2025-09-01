<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708094425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Passer lea colonne eligibilite_tests.requerant_id en one-to-many';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_881877c14a93daa5');
        $this->addSql('CREATE INDEX IDX_TEST_ELIG_REQUERANT ON eligibilite_tests (requerant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_TEST_ELIG_REQUERANT');
        $this->addSql('CREATE UNIQUE INDEX uniq_881877c14a93daa5 ON eligibilite_tests (requerant_id)');
    }
}
