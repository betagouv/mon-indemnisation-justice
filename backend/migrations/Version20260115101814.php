<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115101814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajouter les pièces jointes à la déclaration FDO de bris porte';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE declaration_fdo_bris_porte_pieces_jointes (declaration_id UUID NOT NULL, document INT NOT NULL, PRIMARY KEY(declaration_id, document))');
        $this->addSql('CREATE INDEX IDX_7FF00AAAC06258A3 ON declaration_fdo_bris_porte_pieces_jointes (declaration_id)');
        $this->addSql('CREATE INDEX IDX_7FF00AAAD8698A76 ON declaration_fdo_bris_porte_pieces_jointes (document)');
        $this->addSql('COMMENT ON COLUMN declaration_fdo_bris_porte_pieces_jointes.declaration_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes ADD CONSTRAINT FK_7FF00AAAC06258A3 FOREIGN KEY (declaration_id) REFERENCES declarations_fdo_bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes ADD CONSTRAINT FK_7FF00AAAD8698A76 FOREIGN KEY (document) REFERENCES document (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes DROP CONSTRAINT FK_7FF00AAAC06258A3');
        $this->addSql('ALTER TABLE declaration_fdo_bris_porte_pieces_jointes DROP CONSTRAINT FK_7FF00AAAD8698A76');
        $this->addSql('DROP TABLE declaration_fdo_bris_porte_pieces_jointes');
    }
}
