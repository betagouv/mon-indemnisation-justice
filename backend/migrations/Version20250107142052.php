<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250107142052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Amélioration de la suppression en cascade';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED9450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT FK_881877C14A93DAA5');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT FK_881877C14A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76BCC8DD14');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BCC8DD14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76bcc8dd14');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76bcc8dd14 FOREIGN KEY (liasse_documentaire_id) REFERENCES liasse_documentaire (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE eligibilite_tests DROP CONSTRAINT fk_881877c14a93daa5');
        $this->addSql('ALTER TABLE eligibilite_tests ADD CONSTRAINT fk_881877c14a93daa5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed9450ce1e');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed9450ce1e FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
