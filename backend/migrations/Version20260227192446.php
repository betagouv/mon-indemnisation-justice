<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227192446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommer bris_porte en dossiers et requerants en usagers';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP COLUMN numero_pv');
        $this->addSql('ALTER TABLE bris_porte RENAME TO dossiers');
        $this->addSql('ALTER SEQUENCE bris_porte_id_seq RENAME TO dossiers_id_seq');
        $this->addSql('ALTER TABLE requerants RENAME TO usagers');
        $this->addSql('ALTER SEQUENCE requerants_id_seq RENAME TO usagers_id_seq');

        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT fk_bc580eed4a93daa5');
        $this->addSql('DROP INDEX idx_bc580eed4a93daa5');
        $this->addSql('ALTER TABLE dossiers RENAME COLUMN requerant_id TO usager_id');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT FK_A38E22E44F36F0FC FOREIGN KEY (usager_id) REFERENCES usagers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A38E22E44F36F0FC ON dossiers (usager_id)');
        $this->addSql('ALTER INDEX idx_bc580eed764d0490 RENAME TO IDX_A38E22E4764D0490');
        $this->addSql('ALTER INDEX uniq_bc580eedf90da413 RENAME TO UNIQ_A38E22E4F90DA413');
        $this->addSql('ALTER INDEX uniq_bc580eed9450ce1e RENAME TO UNIQ_A38E22E49450CE1E');
        $this->addSql('ALTER INDEX uniq_bc580eedc06258a3 RENAME TO UNIQ_A38E22E4C06258A3');
        $this->addSql('ALTER INDEX idx_bc580eed4de7dc5c RENAME TO IDX_A38E22E44DE7DC5C');
        $this->addSql('ALTER INDEX uniq_41cf50c44de7dc5c RENAME TO UNIQ_3630FCE34DE7DC5C');
        $this->addSql('ALTER INDEX uniq_41cf50c454472ac9 RENAME TO UNIQ_3630FCE354472AC9');
        $this->addSql('ALTER INDEX uniq_41cf50c435fe3bf6 RENAME TO UNIQ_3630FCE335FE3BF6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX uniq_3630fce335fe3bf6 RENAME TO uniq_41cf50c435fe3bf6');
        $this->addSql('ALTER INDEX uniq_3630fce34de7dc5c RENAME TO uniq_41cf50c44de7dc5c');
        $this->addSql('ALTER INDEX uniq_3630fce354472ac9 RENAME TO uniq_41cf50c454472ac9');
        $this->addSql('ALTER TABLE dossiers DROP CONSTRAINT FK_A38E22E44F36F0FC');
        $this->addSql('DROP INDEX IDX_A38E22E44F36F0FC');
        $this->addSql('ALTER TABLE dossiers RENAME COLUMN usager_id TO requerant_id');
        $this->addSql('ALTER TABLE dossiers ADD CONSTRAINT fk_bc580eed4a93daa5 FOREIGN KEY (requerant_id) REFERENCES usagers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_bc580eed4a93daa5 ON dossiers (requerant_id)');
        $this->addSql('ALTER INDEX idx_a38e22e44de7dc5c RENAME TO idx_bc580eed4de7dc5c');
        $this->addSql('ALTER INDEX idx_a38e22e4764d0490 RENAME TO idx_bc580eed764d0490');
        $this->addSql('ALTER INDEX uniq_a38e22e49450ce1e RENAME TO uniq_bc580eed9450ce1e');
        $this->addSql('ALTER INDEX uniq_a38e22e4c06258a3 RENAME TO uniq_bc580eedc06258a3');
        $this->addSql('ALTER INDEX uniq_a38e22e4f90da413 RENAME TO uniq_bc580eedf90da413');
        $this->addSql('ALTER TABLE usagers RENAME TO requerants');
        $this->addSql('ALTER SEQUENCE usagers_id_seq RENAME TO requerants_id_seq');
        $this->addSql('ALTER TABLE dossiers ADD COLUMN numero_pv INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossiers RENAME TO bris_porte');
        $this->addSql('ALTER SEQUENCE dossiers_id_seq RENAME TO bris_porte_id_seq');
    }
}
