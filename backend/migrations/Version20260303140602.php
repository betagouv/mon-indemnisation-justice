<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303140602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Corriger le rapportAuLogement du test d'éligibilité";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests ALTER rapport_au_logement TYPE VARCHAR(16)');
        $this->addSql('ALTER INDEX uniq_56031d2ae4239e5a RENAME TO UNIQ_CD3370B2E4239E5A');
        $this->addSql('ALTER INDEX uniq_5c2b29a2a21bd112 RENAME TO UNIQ_1EECB5F1A21BD112');
        $this->addSql('ALTER INDEX uniq_5c2b29a24de7dc5c RENAME TO UNIQ_1EECB5F14DE7DC5C');
        $this->addSql('ALTER INDEX idx_5c2b29a2ef5a5513 RENAME TO IDX_1EECB5F1EF5A5513');
        $this->addSql('ALTER INDEX idx_5c2b29a2695c56a1 RENAME TO IDX_1EECB5F1695C56A1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eligibilite_tests ALTER rapport_au_logement TYPE VARCHAR(3)');
        $this->addSql('ALTER INDEX idx_1eecb5f1695c56a1 RENAME TO idx_5c2b29a2695c56a1');
        $this->addSql('ALTER INDEX idx_1eecb5f1ef5a5513 RENAME TO idx_5c2b29a2ef5a5513');
        $this->addSql('ALTER INDEX uniq_1eecb5f14de7dc5c RENAME TO uniq_5c2b29a24de7dc5c');
        $this->addSql('ALTER INDEX uniq_1eecb5f1a21bd112 RENAME TO uniq_5c2b29a2a21bd112');
        $this->addSql('ALTER INDEX uniq_cd3370b2e4239e5a RENAME TO uniq_56031d2ae4239e5a');
    }
}
