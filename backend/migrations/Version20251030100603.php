<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030100603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Lier la déclaration d'erreur opérationnelle à un dossier";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD dossier_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle ADD CONSTRAINT FK_AFEDB022611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AFEDB022611C0C56 ON declarations_erreur_operationnelle (dossier_id)');
        $this->addSql('ALTER TABLE requerants ADD navigation JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE requerants DROP navigation');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP CONSTRAINT FK_AFEDB022611C0C56');
        $this->addSql('DROP INDEX UNIQ_AFEDB022611C0C56');
        $this->addSql('ALTER TABLE declarations_erreur_operationnelle DROP dossier_id');
    }
}
