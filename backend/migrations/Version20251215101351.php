<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215101351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertir le champ doute en type de déclaration';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD est_erreur VARCHAR(6) NOT NULL');
        $this->addSql("UPDATE declarations_erreur_operationnelle SET est_erreur = 'DOUTE' where doute = true");
        $this->addSql("UPDATE declarations_erreur_operationnelle SET est_erreur = 'OUI' where doute = false");
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP doute');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle RENAME COLUMN motif_doute TO description_erreur');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle RENAME TO declarations_fdo_bris_porte');
        $this->addSql('ALTER INDEX declaration_erreur_operationnelle_agent_idx RENAME TO declarations_fdo_bris_porte_agent_idx');
        $this->addSql('ALTER INDEX declaration_erreur_operationnelle_reference_idx RENAME TO declarations_fdo_bris_porte_reference_idx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte RENAME TO declarations_erreur_operationnelle');
        $this->addSql('ALTER INDEX declarations_fdo_bris_porte_agent_idx RENAME TO declaration_erreur_operationnelle_agent_idx');
        $this->addSql('ALTER INDEX declarations_fdo_bris_porte_reference_idx RENAME TO declaration_erreur_operationnelle_reference_idx');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD doute BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP est_erreur');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle RENAME COLUMN description_erreur TO motif_doute');
    }
}
