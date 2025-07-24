<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306154527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champs corps_courrier à la table bris_porte';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD corps_courrier TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ALTER proposition_indemnisation TYPE DOUBLE PRECISION');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP corps_courrier');
        $this->addSql('ALTER TABLE bris_porte ALTER proposition_indemnisation TYPE NUMERIC(10, 2)');
    }
}
