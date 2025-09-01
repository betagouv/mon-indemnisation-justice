<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221102847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Renommage des intitulés d'état de dossier";
    }

    public function up(Schema $schema): void
    {
        $this->addSql("update dossier_etats set etat = 'A_FINALISER' where etat = 'DOSSIER_INITIE'");
        $this->addSql("update dossier_etats set etat = 'A_INSTRUIRE' where etat = 'DOSSIER_DEPOSE'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("update dossier_etats set etat = 'DOSSIER_DEPOSE' where etat = 'A_INSTRUIRE'");
        $this->addSql("update dossier_etats set etat = 'DOSSIER_INITIE' where etat = 'A_FINALISER'");
    }
}
