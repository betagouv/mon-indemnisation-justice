<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240823054951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert JSON roles to basic array';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE IF EXISTS messenger_messages_id_seq CASCADE');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
        $this->addSql('ALTER TABLE "user" ADD COLUMN roles_temp TEXT');
        $this->addSql(<<<SQL
update "user"
set roles_temp = u1.new_roles
from (
    select id, string_agg(ur.roles, ',') as new_roles
    from "user" u
        left join lateral json_array_elements_text(roles) ur(roles) on true
    group by id
) u1
where "user".id = u1.id
SQL);
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" RENAME roles_temp to roles');
        $this->addSql('COMMENT ON COLUMN "user".roles IS \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ALTER roles TYPE JSON USING roles::json');
        $this->addSql('COMMENT ON COLUMN "user".roles IS NULL');
    }
}
