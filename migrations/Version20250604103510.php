<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604103510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Un département peut ne pas avoir de région, une commune pas de département';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_communes ALTER departement_code DROP NOT NULL');
        $this->addSql('ALTER TABLE geo_departements ALTER region_code DROP NOT NULL');
        $this->addSql('ALTER TABLE geo_departements ALTER nom TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE geo_communes ALTER departement_code SET NOT NULL');
        $this->addSql('ALTER TABLE geo_departements ALTER region_code SET NOT NULL');
        $this->addSql('ALTER TABLE geo_departements ALTER nom TYPE VARCHAR(32)');
    }
}
