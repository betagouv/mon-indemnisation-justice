<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261029100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Conversion des brouillons de décla FDO en déclarations non soumises, puis suppression de la table des brouillons';
    }

    public function up(Schema $schema): void
    {
        // Une déclaration non soumise (i.e. un brouillon) n'a ni date de soumission, ni code d'invitation, et ses données
        // peuvent être incomplètes.
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER reference DROP NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER est_erreur DROP NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER date_soumission DROP NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER adresse_id DROP NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER procedure_id DROP NOT NULL');

        /*
         * Conversion des brouillons, directement depuis leurs données JSON. La CTE `brouillons` réserve les identifiants
         * des entités liées à créer : référencée plusieurs fois, elle n'est évaluée qu'une seule fois. Les contraintes de
         * clés étrangères ne sont vérifiées qu'en fin de requête, une fois toutes les insertions jouées.
         */
        $this->addSql(
            <<<SQL
WITH brouillons AS (
    SELECT
        id,
        agent_id,
        date_creation,
        donnees::jsonb AS donnees,
        CASE WHEN jsonb_typeof(donnees::jsonb->'adresse') = 'object' THEN nextval(pg_get_serial_sequence('adresse', 'id')) END AS adresse_id,
        CASE WHEN jsonb_typeof(donnees::jsonb->'procedure') = 'object' THEN gen_random_uuid() END AS procedure_id,
        CASE WHEN jsonb_typeof(donnees::jsonb->'coordonneesRequerant') = 'object' THEN nextval(pg_get_serial_sequence('coordonnees_requerant', 'id')) END AS coordonnees_requerant_id
    FROM brouillons_declaration_fdo_bris_porte
), adresses AS (
    INSERT INTO adresse (id, ligne1, ligne2, code_postal, localite)
    SELECT
        adresse_id,
        donnees->'adresse'->>'ligne1',
        donnees->'adresse'->>'ligne2',
        donnees->'adresse'->>'codePostal',
        donnees->'adresse'->>'localite'
    FROM brouillons
    WHERE adresse_id IS NOT NULL
), procedures AS (
    INSERT INTO procedures_judiciaires (id, numero_procedure, service_enqueteur, telephone, juridiction_ou_parquet, nom_magistrat)
    SELECT
        procedure_id,
        COALESCE(donnees->'procedure'->>'numeroProcedure', ''),
        COALESCE(donnees->'procedure'->>'serviceEnqueteur', ''),
        LEFT(COALESCE(donnees->'procedure'->>'telephone', ''), 20),
        donnees->'procedure'->>'juridictionOuParquet',
        donnees->'procedure'->>'nomMagistrat'
    FROM brouillons
    WHERE procedure_id IS NOT NULL
), coordonnees AS (
    INSERT INTO coordonnees_requerant (id, civilite, nom, prenom, telephone, courriel)
    SELECT
        coordonnees_requerant_id,
        CASE WHEN donnees->'coordonneesRequerant'->>'civilite' IN ('M', 'MME') THEN donnees->'coordonneesRequerant'->>'civilite' END,
        COALESCE(donnees->'coordonneesRequerant'->>'nom', ''),
        COALESCE(donnees->'coordonneesRequerant'->>'prenom', ''),
        COALESCE(donnees->'coordonneesRequerant'->>'telephone', ''),
        COALESCE(donnees->'coordonneesRequerant'->>'courriel', '')
    FROM brouillons
    WHERE coordonnees_requerant_id IS NOT NULL
)
INSERT INTO declarations_fdo_bris_porte (id, adresse_id, procedure_id, coordonnees_requerant_id, agent_id, est_erreur, description_erreur, date_operation, precisions_requerant, date_creation)
SELECT
    id,
    adresse_id,
    procedure_id,
    coordonnees_requerant_id,
    agent_id,
    CASE WHEN donnees->>'estErreur' IN ('OUI', 'DOUTE', 'NON') THEN donnees->>'estErreur' END,
    donnees->>'descriptionErreur',
    CASE WHEN donnees->>'dateOperation' ~ '^\d{4}-\d{2}-\d{2}$' THEN (donnees->>'dateOperation')::DATE ELSE date_creation::DATE END,
    donnees->>'precisionsRequerant',
    date_creation
FROM brouillons
SQL
        );

        // Rattachement des pièces jointes, pour peu que le document existe toujours
        $this->addSql(
            <<<SQL
INSERT INTO declaration_fdo_bris_porte_pieces_jointes (declaration_id, document)
SELECT DISTINCT b.id, d.id
FROM brouillons_declaration_fdo_bris_porte b
    CROSS JOIN LATERAL jsonb_array_elements(CASE WHEN jsonb_typeof(b.donnees::jsonb->'piecesJointes') = 'array' THEN b.donnees::jsonb->'piecesJointes' ELSE '[]'::jsonb END) pj
    INNER JOIN document d ON d.id::TEXT = pj->>'id'
SQL
        );

        $this->addSql('DROP TABLE brouillons_declaration_fdo_bris_porte');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE brouillons_declaration_fdo_bris_porte (id UUID NOT NULL, agent_id INT DEFAULT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, donnees JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C88D0A73414710B ON brouillons_declaration_fdo_bris_porte (agent_id)');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN brouillons_declaration_fdo_bris_porte.date_creation IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE brouillons_declaration_fdo_bris_porte ADD CONSTRAINT FK_5C88D0A73414710B FOREIGN KEY (agent_id) REFERENCES agents (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Reconversion des déclarations non soumises en brouillons
        $this->addSql(
            <<<SQL
INSERT INTO brouillons_declaration_fdo_bris_porte (id, agent_id, date_creation, donnees)
SELECT
    d.id,
    d.agent_id,
    d.date_creation,
    json_build_object(
        'id', d.id,
        'estErreur', d.est_erreur,
        'dateOperation', to_char(d.date_operation, 'YYYY-MM-DD'),
        'descriptionErreur', d.description_erreur,
        'adresse', CASE WHEN a.id IS NOT NULL THEN json_build_object('ligne1', a.ligne1, 'ligne2', a.ligne2, 'codePostal', a.code_postal, 'localite', a.localite) END,
        'procedure', CASE WHEN p.id IS NOT NULL THEN json_build_object('numeroProcedure', p.numero_procedure, 'serviceEnqueteur', p.service_enqueteur, 'telephone', p.telephone, 'juridictionOuParquet', p.juridiction_ou_parquet, 'nomMagistrat', p.nom_magistrat) END,
        'precisionsRequerant', d.precisions_requerant,
        'coordonneesRequerant', CASE WHEN c.id IS NOT NULL THEN json_build_object('civilite', c.civilite, 'nom', c.nom, 'prenom', c.prenom, 'telephone', c.telephone, 'courriel', c.courriel) END,
        'piecesJointes', COALESCE(
            (
                SELECT json_agg(json_build_object('id', doc.id, 'filename', doc.filename, 'type', doc.type, 'originalFilename', doc.original_filename, 'mime', doc.mime, 'size', doc.size, 'fileHash', md5(doc.filename), 'estAjoutRequerant', doc.est_ajout_requerant))
                FROM declaration_fdo_bris_porte_pieces_jointes pj
                    INNER JOIN document doc ON doc.id = pj.document
                WHERE pj.declaration_id = d.id
            ),
            '[]'::json
        )
    )
FROM declarations_fdo_bris_porte d
    LEFT JOIN adresse a ON a.id = d.adresse_id
    LEFT JOIN procedures_judiciaires p ON p.id = d.procedure_id
    LEFT JOIN coordonnees_requerant c ON c.id = d.coordonnees_requerant_id
WHERE d.date_soumission IS NULL
SQL
        );

        $this->addSql('DELETE FROM declaration_fdo_bris_porte_pieces_jointes WHERE declaration_id IN (SELECT id FROM declarations_fdo_bris_porte WHERE date_soumission IS NULL)');
        $this->addSql(
            <<<SQL
WITH declarations_supprimees AS (
    DELETE FROM declarations_fdo_bris_porte WHERE date_soumission IS NULL
    RETURNING adresse_id, procedure_id, coordonnees_requerant_id
), adresses_supprimees AS (
    DELETE FROM adresse WHERE id IN (SELECT adresse_id FROM declarations_supprimees)
), procedures_supprimees AS (
    DELETE FROM procedures_judiciaires WHERE id IN (SELECT procedure_id FROM declarations_supprimees)
)
DELETE FROM coordonnees_requerant WHERE id IN (SELECT coordonnees_requerant_id FROM declarations_supprimees)
SQL
        );

        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER reference SET NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER est_erreur SET NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER date_soumission SET NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER adresse_id SET NOT NULL');
        $this->addSql('ALTER TABLE declarations_fdo_bris_porte ALTER procedure_id SET NOT NULL');
    }
}
