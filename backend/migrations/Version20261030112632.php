<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261030112632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ne pas supprimer une déclaration quand le bris de porte lié est supprimé';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT fk_74ec5cf73c487237');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73C487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73C487237');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT fk_74ec5cf73c487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
