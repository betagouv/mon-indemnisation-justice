<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304153008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convertir les dossiers à finaliser en brouillons';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL


-- INSERT INTO brouillons (id, requerant_id, agent_id, type, date_creation, donnees)
SELECT
    gen_random_uuid() as id,
    d.usager_id as requerant_id,
    d.redacteur_id as agent_id,
    'BROUILLON_DOSSIER_BRIS_PORTE' as type,
    d.date_creation as date_creation,
    json_strip_nulls(json_build_object(
        'usager', u.id,
        'requerant', CASE
                WHEN pm.id is not null THEN
                    json_strip_nulls(json_build_object(
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
                ELSE json_strip_nulls(json_build_object(
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
                     'paysNaissance', pp.pays_naissance,
                     -- TODO intégrer les données de la commune
                     'communeNaissance', NULL,
                    -- TODO ajouter la colonne ville_naissance
                     'villeNaissance', NULL

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
        'dateOperation', bp.date_operation,
        'idTestEligibilite', bp.test_eligibilite_id,
        'idDeclarationFDO', bp.declaration_id,
        'estPorteBlindee', bp.est_porte_blindee
        -- TODO intégrer les pièces jointes
    )) as donnees
FROM dossiers d
    INNER JOIN dossier_etats ed on d.etat_actuel_id = ed.id
    LEFT JOIN usagers u ON d.usager_id = u.id
    LEFT JOIN bris_porte bp ON d.bris_porte_id = bp.id
    LEFT JOIN adresse a ON bp.adresse_id = a.id
    LEFT JOIN personnes_physiques pp ON d.requerant_personne_physique_id = pp.id
    LEFT JOIN personnes p1 on pp.personne_id = p1.id
    LEFT JOIN adresse a2 ON pp.adresse_id = a2.id
    LEFT JOIN personnes_morales pm ON d.requerant_personne_morale_id = pm.id
    LEFT JOIN personnes p2 on pm.representant_legal_id = p2.id
WHERE
    d.type = 'BRIS_PORTE'
    AND ed.etat = 'A_FINALISER'
SQL
        );
        // TODO supprimer les dossiers et leur état
    }

    public function down(Schema $schema): void
    {

    }
}
