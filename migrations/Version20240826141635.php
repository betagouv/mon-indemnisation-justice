<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240826141635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jour de l\'entité Statut et suppression de l\'entité `Statut`';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE if exists messenger_messages_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tracking_id_seq CASCADE');
        $this->addSql('ALTER TABLE tracking DROP CONSTRAINT fk_a87c621c9b6b5fba');
        $this->addSql('DROP TABLE tracking');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT fk_e564f0bf79e92e8c');
        $this->addSql('DROP INDEX idx_e564f0bf79e92e8c');
        $this->addSql('ALTER TABLE statut ADD requerant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statut ADD agent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statut DROP emetteur_id');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF4A93DAA5 FOREIGN KEY (requerant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF3414710B FOREIGN KEY (agent_id) REFERENCES agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E564F0BF4A93DAA5 ON statut (requerant_id)');
        $this->addSql('CREATE INDEX IDX_E564F0BF3414710B ON statut (agent_id)');
        $this->addSql('ALTER TABLE "user" ALTER roles TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF3414710B');
        $this->addSql('CREATE SEQUENCE tracking_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tracking (id SERIAL NOT NULL, account_id INT NOT NULL, event VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_a87c621c9b6b5fba ON tracking (account_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tracking ADD CONSTRAINT fk_a87c621c9b6b5fba FOREIGN KEY (account_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE agents');
        $this->addSql('ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF4A93DAA5');
        $this->addSql('DROP INDEX IDX_E564F0BF4A93DAA5');
        $this->addSql('DROP INDEX IDX_E564F0BF3414710B');
        $this->addSql('ALTER TABLE statut ADD emetteur_id INT NOT NULL');
        $this->addSql('ALTER TABLE statut DROP requerant_id');
        $this->addSql('ALTER TABLE statut DROP agent_id');
        $this->addSql('ALTER TABLE statut ADD CONSTRAINT fk_e564f0bf79e92e8c FOREIGN KEY (emetteur_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e564f0bf79e92e8c ON statut (emetteur_id)');
        $this->addSql('ALTER TABLE "user" ALTER roles TYPE JSON');
        $this->addSql('COMMENT ON COLUMN "user".roles IS NULL');
    }
}
