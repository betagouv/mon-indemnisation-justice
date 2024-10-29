<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029110052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sauvegarder les déclarations du test d\'éligibilité';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ADD est_vise BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD est_hebergeant BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD est_proprietaire BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD a_contact_assurance BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE bris_porte ADD a_contact_bailleur BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bris_porte DROP est_vise');
        $this->addSql('ALTER TABLE bris_porte DROP est_hebergeant');
        $this->addSql('ALTER TABLE bris_porte DROP est_proprietaire');
        $this->addSql('ALTER TABLE bris_porte DROP a_contact_assurance');
        $this->addSql('ALTER TABLE bris_porte DROP a_contact_bailleur');
    }
}
