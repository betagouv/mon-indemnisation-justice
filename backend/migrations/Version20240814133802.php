<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240814133802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Auto increment entity ids on insert';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  adresse_id_seq');
        $this->addSql('SELECT setval(\'adresse_id_seq\', (SELECT MAX(id) FROM adresse))');
        $this->addSql('ALTER TABLE adresse ALTER id SET DEFAULT nextval(\'adresse_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  categorie_id_seq');
        $this->addSql('SELECT setval(\'categorie_id_seq\', (SELECT MAX(id) FROM categorie))');
        $this->addSql('ALTER TABLE categorie ALTER id SET DEFAULT nextval(\'categorie_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  civilite_id_seq');
        $this->addSql('SELECT setval(\'civilite_id_seq\', (SELECT MAX(id) FROM civilite))');
        $this->addSql('ALTER TABLE civilite ALTER id SET DEFAULT nextval(\'civilite_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  document_id_seq');
        $this->addSql('SELECT setval(\'document_id_seq\', (SELECT MAX(id) FROM document))');
        $this->addSql('ALTER TABLE document ALTER id SET DEFAULT nextval(\'document_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  liasse_documentaire_id_seq');
        $this->addSql('SELECT setval(\'liasse_documentaire_id_seq\', (SELECT MAX(id) FROM liasse_documentaire))');
        $this->addSql('ALTER TABLE liasse_documentaire ALTER id SET DEFAULT nextval(\'liasse_documentaire_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  personne_morale_id_seq');
        $this->addSql('SELECT setval(\'personne_morale_id_seq\', (SELECT MAX(id) FROM personne_morale))');
        $this->addSql('ALTER TABLE personne_morale ALTER id SET DEFAULT nextval(\'personne_morale_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  personne_physique_id_seq');
        $this->addSql('SELECT setval(\'personne_physique_id_seq\', (SELECT MAX(id) FROM personne_physique))');
        $this->addSql('ALTER TABLE personne_physique ALTER id SET DEFAULT nextval(\'personne_physique_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  prejudice_id_seq');
        $this->addSql('SELECT setval(\'prejudice_id_seq\', (SELECT MAX(id) FROM prejudice))');
        $this->addSql('ALTER TABLE prejudice ALTER id SET DEFAULT nextval(\'prejudice_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  qualite_requerant_id_seq');
        $this->addSql('SELECT setval(\'qualite_requerant_id_seq\', (SELECT MAX(id) FROM qualite_requerant))');
        $this->addSql('ALTER TABLE qualite_requerant ALTER id SET DEFAULT nextval(\'qualite_requerant_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  service_enqueteur_id_seq');
        $this->addSql('SELECT setval(\'service_enqueteur_id_seq\', (SELECT MAX(id) FROM service_enqueteur))');
        $this->addSql('ALTER TABLE service_enqueteur ALTER id SET DEFAULT nextval(\'service_enqueteur_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  statut_id_seq');
        $this->addSql('SELECT setval(\'statut_id_seq\', (SELECT MAX(id) FROM statut))');
        $this->addSql('ALTER TABLE statut ALTER id SET DEFAULT nextval(\'statut_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  tracking_id_seq');
        $this->addSql('SELECT setval(\'tracking_id_seq\', (SELECT MAX(id) FROM tracking))');
        $this->addSql('ALTER TABLE tracking ALTER id SET DEFAULT nextval(\'tracking_id_seq\')');
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS  user_id_seq');
        $this->addSql('SELECT setval(\'user_id_seq\', (SELECT MAX(id) FROM "user"))');
        $this->addSql('ALTER TABLE "user" ALTER id SET DEFAULT nextval(\'user_id_seq\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE categorie ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE statut ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE qualite_requerant ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE liasse_documentaire ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE civilite ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE document ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE prejudice ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE tracking ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE service_enqueteur ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE personne_morale ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE personne_physique ALTER id DROP DEFAULT');
    }
}
