<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213070534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduction du rôle ROLE_AGENT_DOSSIER';
    }

    public function up(Schema $schema): void
    {
        // On remplace le rôle ROLE_AGENT_REDACTEUR par le role ROLE_AGENT_DOSSIER aux agents disposant d'un rôle autre
        // que rédacteur (ROLE_AGENT_GESTION_PERSONNEL, ROLE_AGENT_VALIDATEUR, ROLE_AGENT_ATTRIBUTEUR)
        $this->addSql(<<<SQL
update agents
set roles = q.nouveaux_roles
from (
    select
        id as agent_id,
        roles,
        array_to_string(
            array_append(
                array_remove(
                    string_to_array(roles, ','),
                    'ROLE_AGENT_REDACTEUR'
                ),
                'ROLE_AGENT_DOSSIER'
            ),
            ','
        ) as nouveaux_roles
    from agents
    where
        'ROLE_AGENT_REDACTEUR'  = any(string_to_array(roles, ','))
        AND (
            'ROLE_AGENT_GESTION_PERSONNEL' = any(string_to_array(roles, ','))
            or 'ROLE_AGENT_VALIDATEUR' = any(string_to_array(roles, ','))
            or 'ROLE_AGENT_ATTRIBUTEUR' = any(string_to_array(roles, ','))
        )
) q
where id = q.agent_id
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
