<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261126084804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Ajouter des colonnes d'état à la requête de signature requérant";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD date_activation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD date_annulation TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE signature_requetes_requerant ADD date_expiration TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP date_activation');
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP date_annulation');
        $this->addSql('ALTER TABLE signature_requetes_requerant DROP date_expiration');
    }
}
