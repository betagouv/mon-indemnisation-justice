<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260428102402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ne plus supprimer les données géo lors de la suppression d'une personne physique";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT FK_74EC5CF73C487237');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT FK_74EC5CF73C487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personnes_physiques DROP CONSTRAINT fk_5c2b29a2695c56a1');
        $this->addSql('ALTER TABLE personnes_physiques DROP CONSTRAINT fk_5c2b29a2ef5a5513');
        $this->addSql('ALTER TABLE personnes_physiques ADD CONSTRAINT FK_1EECB5F1EF5A5513 FOREIGN KEY (code_postal_naissance_id) REFERENCES geo_codes_postaux (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personnes_physiques ADD CONSTRAINT FK_1EECB5F1695C56A1 FOREIGN KEY (pays_naissance) REFERENCES geo_pays (code) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_physiques DROP CONSTRAINT FK_1EECB5F1EF5A5513');
        $this->addSql('ALTER TABLE personnes_physiques DROP CONSTRAINT FK_1EECB5F1695C56A1');
        $this->addSql('ALTER TABLE personnes_physiques ADD CONSTRAINT fk_5c2b29a2695c56a1 FOREIGN KEY (pays_naissance) REFERENCES geo_pays (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE personnes_physiques ADD CONSTRAINT fk_5c2b29a2ef5a5513 FOREIGN KEY (code_postal_naissance_id) REFERENCES geo_codes_postaux (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte DROP CONSTRAINT fk_74ec5cf73c487237');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ADD CONSTRAINT fk_74ec5cf73c487237 FOREIGN KEY (bris_porte_id) REFERENCES bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
