<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814134942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Provision static tables';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(
            'insert into qualite_requerant (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [1, "1", "PRO", "Propriétaire"]);
        $this->connection->executeStatement(
            'insert into qualite_requerant (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [2, "2", "LOC", "Locataire"]);
        $this->connection->executeStatement(
            'insert into qualite_requerant (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [3, "3", "HEB", "Hébergeant"]);
        $this->connection->executeStatement(
            'insert into qualite_requerant (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [4, "4", "AUT", "Autre"]);
        $this->connection->executeStatement(
            'insert into civilite (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [1, "1", "M", "Monsieur"]);
        $this->connection->executeStatement(
            'insert into civilite (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [2, "2", "MME", "Madame"]);
        $this->connection->executeStatement(
            'insert into categorie (id, code, mnemo, libelle) values (?, ?, ?, ?) on conflict do nothing',
            [1, "1", "BRI", "Bris de porte"]);
    }

    public function down(Schema $schema): void
    {
        // No down query as we cannot guarantee these tables weren't provisioned yet before migration
    }
}
