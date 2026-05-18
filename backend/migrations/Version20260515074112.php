<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260515074112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter le SIRET de l'administration";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents RENAME donnes_authentification TO donnees_authentification');
        $this->addSql('ALTER TABLE agents ADD administration_siret VARCHAR(14) DEFAULT NULL');
        $this->addSql("UPDATE agents SET administration_siret = donnees_authentification::jsonb->>'siret' WHERE donnees_authentification IS NOT NULL AND donnees_authentification <> '' AND donnees_authentification::jsonb ?? 'siret'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents DROP administration_siret');
        $this->addSql('ALTER TABLE agents RENAME donnees_authentification TO donnes_authentification');
    }
}
