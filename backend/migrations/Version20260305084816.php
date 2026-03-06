<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305084816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertir les dossiers à finaliser en brouillons';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_physiques ADD ville_naissance VARCHAR(255) DEFAULT NULL');
        // Créer les brouillons des dossiers en cours de constitution
        $this->addSql(
            <<<SQL
INSERT INTO brouillons (id, requerant_id, agent_id, type, date_creation, donnees)
SELECT
    gen_random_uuid() as id,
    d.usager_id as requerant_id,
    d.redacteur_id as agent_id,
    'BROUILLON_DOSSIER_BRIS_PORTE' as type,
    d.date_creation as date_creation,
    json_strip_nulls(json_build_object(
        'usager', u.id,
        'personnePhysique', CASE
            WHEN pp.id IS NOT NULL THEN json_strip_nulls(json_build_object(
                     'personne', json_strip_nulls(json_build_object(
                            'id', p1.id,
                            'civilite', p1.civilite,
                            'nom', p1.nom,
                            'nomNaissance', p1.nom_naissance,
                            'prenom', p1.prenom,
                            'courriel', p1.courriel,
                            'telephone', p1.telephone
                     )),
                     'adresse', CASE
                        WHEN a2.id IS NOT NULL THEN json_strip_nulls(json_build_object(
                            'ligne1', a2.ligne1,
                            'ligne2', a2.ligne2,
                            'codePostal', a2.code_postal,
                            'commune', a2.localite
                        ))
                        END,
                     'dateNaissance', pp.date_naissance,
                     'paysNaissance', CASE WHEN gp.code IS NOT NULL THEN json_build_object(
                        'code', gp.code,
                        'nom', gp.nom
                     )
                     END,
                     'communeNaissance', CASE
                            WHEN gcp.id IS NOT NULL THEN json_strip_nulls(
                                json_build_object(
                                    'id', gcp.id,
                                    'codePostal', gcp.code_postal,
                                    'nom', gc.nom,
                                    'departement', gd.nom
                                )
                            ) END

                ))
            END,
        'personneMorale', CASE
            WHEN pm.id is not null THEN json_strip_nulls(json_build_object(
                        'raisonSociale', pm.raison_sociale,
                        'siren', pm.siren_siret,
                        'representantLegal', json_strip_nulls(json_build_object(
                            'id', p2.id,
                            'civilite', p2.civilite,
                            'nom', p2.nom,
                            'nomNaissance', p2.nom_naissance,
                            'prenom', p2.prenom,
                            'courriel', p2.courriel,
                            'telephone', p2.telephone
                        ))
                    ))
            END,
        'rapportAuLogement', bp.rapport_au_logement,
        'descriptionRapportAuLogement', bp.precision_rapport_au_logement,
        'adresse', CASE
            WHEN a.id IS NOT NULL THEN json_strip_nulls(json_build_object(
                'ligne1', a.ligne1,
                'ligne2', a.ligne2,
                'codePostal', a.code_postal,
                'commune', a.localite
            ))
        END,
        'dateOperation', CASE WHEN bp.date_operation IS NOT NULL THEN TO_CHAR(bp.date_operation, 'YYYY-MM-DD') END,
        'description', bp.description_requerant,
        'idTestEligibilite', bp.test_eligibilite_id,
        'idDeclarationFDO', bp.declaration_id,
        'estPorteBlindee', bp.est_porte_blindee,
        'piecesJointes', dd.pieces_jointes
    )) as donnees
FROM dossiers d
    INNER JOIN dossier_etats ed ON d.etat_actuel_id = ed.id
    LEFT JOIN usagers u ON d.usager_id = u.id
    LEFT JOIN bris_porte bp ON d.bris_porte_id = bp.id
    LEFT JOIN adresse a ON bp.adresse_id = a.id
    LEFT JOIN personnes_physiques pp ON d.requerant_personne_physique_id = pp.id
    LEFT JOIN geo_pays gp ON pp.pays_naissance = gp.code
    LEFT JOIN personnes p1 ON pp.personne_id = p1.id
    LEFT JOIN geo_codes_postaux gcp ON pp.code_postal_naissance_id = gcp.id
    LEFT JOIN geo_communes gc ON gcp.code_commune = gc.code
    LEFT JOIN geo_departements gd ON gc.departement_code = gd.code
    LEFT JOIN adresse a2 ON pp.adresse_id = a2.id
    LEFT JOIN personnes_morales pm ON d.requerant_personne_morale_id = pm.id
    LEFT JOIN personnes p2 ON pm.representant_legal_id = p2.id
    LEFT JOIN (
        SELECT dd.dossier_id, json_agg(
                json_build_object(
                      'id', doc.id,
                      'type', doc.type,
                      'chemin', doc.filename,
                      'nom', doc.original_filename,
                      'mime', doc.mime,
                      'taille', doc.size
                )
            ) as pieces_jointes
       FROM document_dossiers dd
           LEFT JOIN document doc ON dd.document_id = doc.id
       GROUP BY dd.dossier_id
) dd ON d.id = dd.dossier_id
WHERE
    d.type = 'BRIS_PORTE'
    AND ed.etat = 'A_FINALISER'
SQL
        );
        // Supprimer les états liés aux dossiers
        $this->addSql(
            <<<SQL
DELETE FROM dossier_etats
WHERE exists(
    SELECT d.id
    FROM dossiers d
        INNER JOIN dossier_etats ed ON d.etat_actuel_id = ed.id AND ed.etat = 'A_FINALISER'
    WHERE
        dossier_etats.dossier_id = d.id
        AND d.type = 'BRIS_PORTE'
)
SQL
        );
        // Supprimer les pièces jointes liées aux dossiers
        $this->addSql(
            <<<SQL
DELETE FROM document_dossiers
WHERE exists(
    SELECT d.id
    FROM dossiers d
        INNER JOIN dossier_etats ed ON d.etat_actuel_id = ed.id AND ed.etat = 'A_FINALISER'
    WHERE
        document_dossiers.dossier_id = d.id
        AND d.type = 'BRIS_PORTE'
)
SQL
        );
        // Supprimer les dossiers
        $this->addSql(
            <<<SQL
DELETE FROM dossiers
WHERE etat_actuel_id is null
    AND type = 'BRIS_PORTE'
SQL
        );
        // Supprimer les bris de porte
        $this->addSql(
            <<<SQL
DELETE FROM bris_porte
WHERE NOT EXISTS(
    SELECT d.id
    FROM dossiers d
    WHERE d.bris_porte_id = bris_porte.id
)
SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnes_physiques DROP ville_naissance');
    }
}
