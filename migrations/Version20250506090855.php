<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506090855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
update dossier_etats
set etat = 'CLOTURE', contexte = '{"motif":"Doublon d''un dossier déposé en version papier"}'
where etat = 'DOUBLON_PAPIER'
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
update dossier_etats
set etat = 'DOUBLON_PAPIER', contexte = null
where etat = 'CLOTURE'
SQL);
    }
}
