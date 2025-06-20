<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620133536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Nettoyage sur les données de naissance';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_pays_code_insee ON geo_pays (code_insee)');
        $this->addSql("update geo_pays set code_insee = '99157' where code = 'KOS'");
        $this->addSql('ALTER TABLE personne_physique DROP commune_naissance');
        $this->addSql(<<<SQL
update personne_physique
set pays_naissance = 'FRA'
where code_postal_naissance_id is not null
    and pays_naissance is null;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_pays_code_insee');
        $this->addSql('ALTER TABLE personne_physique ADD commune_naissance VARCHAR(255) DEFAULT NULL');
    }
}
