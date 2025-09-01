<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830131926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renommage des roles associés aux agents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_ADMIN_FONC', 'ROLE_AGENT_GESTION_PERSONNEL')");
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_REDACTEUR_PRECONTENTIEUX', 'ROLE_AGENT_REDACTEUR')");
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_CHEF_PRECONTENTIEUX', 'ROLE_AGENT_VALIDATEUR')");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_AGENT_VALIDATEUR', 'ROLE_CHEF_PRECONTENTIEUX')");
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_AGENT_REDACTEUR', 'ROLE_REDACTEUR_PRECONTENTIEUX')");
        $this->addSql("update agents set roles = regexp_replace(roles, 'ROLE_AGENT_GESTION_PERSONNEL', 'ROLE_ADMIN_FONC')");


    }
}
