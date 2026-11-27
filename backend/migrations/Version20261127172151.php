<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261127172151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Rattache à la référence bancaire le relevé d'identité bancaire généré (document de type `reference_bancaire`)";
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE references_bancaires ADD document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE references_bancaires ADD CONSTRAINT FK_2CBF3B89C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2CBF3B89C33F7837 ON references_bancaires (document_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE references_bancaires DROP CONSTRAINT FK_2CBF3B89C33F7837');
        $this->addSql('DROP INDEX UNIQ_2CBF3B89C33F7837');
        $this->addSql('ALTER TABLE references_bancaires DROP document_id');
    }
}
