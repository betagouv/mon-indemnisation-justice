<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240924125435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Simplifier les champs booléens de Requerant';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('update bris_porte set is_porte_blindee = false where is_porte_blindee is null');
        $this->addSql('ALTER TABLE bris_porte ALTER is_porte_blindee SET NOT NULL');
        $this->addSql('update bris_porte set is_erreur_porte = false where is_erreur_porte is null');
        $this->addSql('ALTER TABLE bris_porte ALTER is_erreur_porte SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ALTER is_porte_blindee DROP NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ALTER is_erreur_porte DROP NOT NULL');
    }
}
