<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022073907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Création d'une view SQL pour calculer l'administration réelle d'un agent FDO";
    }

    public function up(Schema $schema): void
    {
        /*
         * Si un agent FDO a renseigné son affectation, son administration est celle rattachée à l'établissement.
         * Sinon s'il vient de la PN depuis ProConnect et qu'il a déclaré _au moins un_ bris de porte sur un département
         * de la compétence de la Préfecture de Police alors, il est considéré PP.
         * Sinon son administration demeure celle d'origine.
         */
        $this->addSql(
            <<<SQL
CREATE VIEW agents_administrations AS
SELECT
    agents.id,
    CASE
        WHEN fdo_etablissements.administration_code IS NOT NULL THEN fdo_etablissements.administration_code
            WHEN dpp.id IS NOT NULL AND agents.administration_code = 'PN' THEN 'PP'
        ELSE agents.administration_code END AS administration_code
FROM agents
    LEFT JOIN fdo_affectations ON agents.id = fdo_affectations.agent_id
    LEFT JOIN fdo_etablissements ON fdo_affectations.etablissement_id = fdo_etablissements.id
    LEFT JOIN (
        SELECT DISTINCT ON (d.agent_id) d.agent_id, d.id, SUBSTR(a.code_postal, 1 , 2) AS code_departement
        FROM declarations_fdo_bris_porte d
            INNER JOIN adresse a ON d.adresse_id = a.id
    ) dpp ON dpp.agent_id = agents.id AND dpp.code_departement IN ('75', '92', '93', '94')
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW IF EXISTS agents_administrations');

    }
}
