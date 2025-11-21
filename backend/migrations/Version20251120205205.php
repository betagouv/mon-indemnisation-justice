<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120205205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter les champs liés au doute sur la déclaration d'erreur opérationnelle";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD doute BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD motif_doute TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP doute');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP motif_doute');
    }
}
