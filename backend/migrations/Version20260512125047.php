<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512125047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertir les adresses courriels des usagers en lowercase';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('update usagers set email = lower(email) where email <> lower(email)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
