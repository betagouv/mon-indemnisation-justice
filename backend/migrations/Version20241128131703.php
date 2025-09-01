<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128131703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Retrait des infos receveur attestation et service enquêteur';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ALTER receveur_attestation_id DROP NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ALTER service_enqueteur_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte ALTER receveur_attestation_id SET NOT NULL');
        $this->addSql('ALTER TABLE bris_porte ALTER service_enqueteur_id SET NOT NULL');
    }
}
