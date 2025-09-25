<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924144036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Créer la table de cache pour le limitateur de tentatives de connexion';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
create table _cache_limitateur_connexion
(
    item_id       varchar(255) not null
        primary key,
    item_data     bytea        not null,
    item_lifetime integer,
    item_time     integer      not null
);


SQL);
    }

    public function down(Schema $schema): void
    {

    }
}
