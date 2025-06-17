<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617124042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Affiner les règles de cascade delete';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED4DE7DC5C');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EEDF90DA413');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT FK_BC580EED9450CE1E');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EEDF90DA413 FOREIGN KEY (etat_actuel_id) REFERENCES dossier_etats (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT FK_BC580EED9450CE1E FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT FK_71671FCF611C0C56');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT FK_71671FCF611C0C56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier_etats DROP CONSTRAINT fk_71671fcf611c0c56');
        $this->addSql('ALTER TABLE dossier_etats ADD CONSTRAINT fk_71671fcf611c0c56 FOREIGN KEY (dossier_id) REFERENCES bris_porte (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eedf90da413');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed4de7dc5c');
        $this->addSql('ALTER TABLE bris_porte DROP CONSTRAINT fk_bc580eed9450ce1e');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eedf90da413 FOREIGN KEY (etat_actuel_id) REFERENCES dossier_etats (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed4de7dc5c FOREIGN KEY (adresse_id) REFERENCES adresse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bris_porte ADD CONSTRAINT fk_bc580eed9450ce1e FOREIGN KEY (test_eligibilite_id) REFERENCES eligibilite_tests (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
