<?php

namespace App\Repository;

use App\Contracts\PrejudiceInterface;
use App\Entity\BrisPorte;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrisPorte>
 *
 * @method BrisPorte|null find($id, $lockMode = null, $lockVersion = null)
 * @method BrisPorte|null findOneBy(array $criteria, array $orderBy = null)
 * @method BrisPorte[]    findAll()
 * @method BrisPorte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrisPorteRepository extends ServiceEntityRepository
{
    use PrejudiceRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrisPorte::class);
    }

    public function get(BrisPorte $brisPorte): array
    {
        $brisPorteId = $brisPorte->getId();
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
        SELECT
        	b.id,
        	--adresse_id, qualite_requerant_id,receveur_attestation_id, service_enqueteur_id ,
        	b.numero_pv,
        	b.date_operation_pj,
        	p.date_declaration,
        	p.reference,
        	--p.liasse_document_id,
        	b.is_porte_blindee,
        	b.is_erreur_porte,
        	b.identite_personne_recherchee,
        	b.nom_remise_attestation,
        	b.prenom_remise_attestation,
        	b.precision_requerant,
        	b.date_attestation_information,
        	b.numero_parquet,
          u.id user_id,
          u.is_personne_morale,
          pm.raison_sociale,
          pm.siren_siret,
          a.ligne1,
          a.code_postal,
          a.localite,
          c.libelle AS civilite_libelle,
          pp.prenom1,
          pp.nom,
          ra.prenom1 ra_prenom1,
          ra.nom ra_nom,
          cra.libelle ra_civilite_libelle,
          se.nom AS se_nom,
          se.numero_pv,
          se.telephone AS se_telephone,
          se.courriel AS se_courriel,
          se.juridiction,
          se.numero_parquet,
          se.magistrat,
          CASE
            WHEN qr.libelle IS NOT NULL THEN qr.libelle ||' - '|| b.precision_requerant
            ELSE 'non renseigné'
          END qualite_requerant,
          CASE
            WHEN ppqr.libelle IS NOT NULL THEN ppqr.libelle ||' - '|| ra.precision
            ELSE 'non renseigné'
          END qualite_receveur_attestation,
          bad.ligne1 bp_ligne1,
          bad.code_postal bp_code_postal,
          bad.localite bp_localite
      	FROM public.bris_porte b
        INNER JOIN public.prejudice p ON b.id=p.id
        INNER JOIN public.user u ON u.id=p.requerant_id
        LEFT JOIN public.adresse bad ON bad.id = b.adresse_id
        LEFT JOIN public.qualite_requerant qr ON qr.id = b.qualite_requerant_id
        LEFT JOIN public.service_enqueteur se ON se.id = b.service_enqueteur_id
        LEFT JOIN public.personne_physique ra ON ra.id = b.receveur_attestation_id
        LEFT JOIN public.adresse a ON a.id = u.adresse_id
      	LEFT JOIN public.personne_physique pp ON pp.id=u.personne_physique_id
        LEFT JOIN public.civilite c ON c.id = pp.civilite_id
      	LEFT JOIN public.personne_morale pm ON pm.id=u.personne_morale_id
        LEFT JOIN public.civilite cra ON cra.id = ra.civilite_id
        LEFT JOIN public.qualite_requerant ppqr ON ppqr.id = ra.qualite_id
      	WHERE b.id = $brisPorteId
        ";
        $stmt = $conn->query($sql);
        $data = [
          'brisPorte' => [
            'serviceEnqueteur' => [],
            'adresse' => [],
            'receveurAttestation' => [],
          ],
          'user' => [
            'personnePhysique' => [],
            'personneMorale' => [],
          ]
        ];
        $tmp = $stmt->fetchAll();
        if(count($tmp)) {
          $bpFields = [
            "id","numero_pv", "dateOperationPJ" => "date_operation_pj",
            "date_declaration", "reference", "isPorteBlindee" => "is_porte_blindee",
            "isErreurPorte" => "is_erreur_porte", "identite_personne_recherchee",
            "nom_remise_attestation", "prenom_remise_attestation",
            "precision_requerant", "date_attestation_information",
            "numero_parquet","qualiteRequerant" => "qualite_requerant"
          ];
          foreach($bpFields as $index => $bpField) {
            $key = !is_int($index) ? $index : $bpField;
            $data['brisPorte'][$key] = $tmp[0][$bpField];
          }
          $bpaFields = [
            "ligne1" => "bp_ligne1",
            "codePostal" => "bp_code_postal",
            "localite" => "bp_localite",
          ];
          foreach($bpaFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['brisPorte']['adresse'][$key] = $tmp[0][$field];
          }
          $uFields=[
            "isPersonneMorale" => "is_personne_morale",
          ];
          foreach($uFields as $index => $uField) {
            $key = !is_int($index) ? $index : $uField;
            $data['user'][$key] = $tmp[0][$uField];
          }
          $pmFields=[
            "raisonSociale" => "raison_sociale",
            "sirenSiret" => "siren_siret",
          ];
          foreach($pmFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['user']['personneMorale'][$key] = $tmp[0][$field];
          }
          $ppFields=[
            "civilite" => "civilite_libelle",
            "prenom1",
            "nom",
          ];
          foreach($ppFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['user']['personnePhysique'][$key] = $tmp[0][$field];
          }
          $raFields=[
            "civilite" => "ra_civilite_libelle",
            "prenom1" => "ra_prenom1",
            "nom" => "ra_nom",
            "qualite" => "qualite_receveur_attestation",
          ];
          foreach($raFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['brisPorte']['receveurAttestation'][$key] = $tmp[0][$field];
          }
          $aFields=[
            "ligne1",
            "codePostal" => "code_postal",
            "localite",
          ];
          foreach($aFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['user']['adresse'][$key] = $tmp[0][$field];
          }
          $seFields=[
            "nom" => "se_nom",
            "numeroPV" => "numero_pv",
            "telephone" => "se_telephone",
            "courriel" => "se_courriel",
            "juridiction",
            "numeroParquet" => "numero_parquet",
            "magistrat"
          ];
          foreach($seFields as $index => $field) {
            $key = !is_int($index) ? $index : $field;
            $data['brisPorte']['serviceEnqueteur'][$key] = $tmp[0][$field];
          }
        }
        return $data;
    }
}
