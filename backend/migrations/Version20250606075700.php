<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606075700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE institutions_securite_publique (type VARCHAR(2) NOT NULL, date_integration DATE DEFAULT NULL, PRIMARY KEY(type))');
        $this->addSql('ALTER TABLE bris_porte ADD type_institution_securite_publique VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD est_lie_attestation BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED69C2E78D FOREIGN KEY (type_institution_securite_publique) REFERENCES institutions_securite_publique (type) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BC580EED69C2E78D ON bris_porte (type_institution_securite_publique)');
        $this->addSql("insert into institutions_securite_publique (type, date_integration) values ('PN', '2024-11-07'), ('GN', '2025-03-20'), ('PP', null)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED69C2E78D');
        $this->addSql('DROP TABLE institutions_securite_publique');
        $this->addSql('DROP INDEX IDX_BC580EED69C2E78D');
        $this->addSql('ALTER TABLE bris_porte DROP type_institution_securite_publique');
        $this->addSql('ALTER TABLE bris_porte DROP est_lie_attestation');
    }
}
