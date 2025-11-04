<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104101634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corriger la FK declaration -> dossier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT FK_AFEDB022611C0C56');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT FK_AFEDB022611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT fk_afedb022611c0c56');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT fk_afedb022611c0c56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
