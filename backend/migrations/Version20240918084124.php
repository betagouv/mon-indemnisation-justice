<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240918084124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression en cascade requerant vers bris_porte';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4A93DAA5');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed4a93daa5');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed4a93daa5 FOREIGN KEY (requerant_id) REFERENCES requerants (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
