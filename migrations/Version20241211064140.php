<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211064140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Déclaration du jeton de vérification agent';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents ADD jeton_verification VARCHAR(12) DEFAULT NULL');
        $this->addSql('ALTER TABLE agents ALTER mot_de_passe DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agents ALTER mot_de_passe SET NOT NULL');
        $this->addSql('ALTER TABLE agents DROP jeton_verification');
    }
}
