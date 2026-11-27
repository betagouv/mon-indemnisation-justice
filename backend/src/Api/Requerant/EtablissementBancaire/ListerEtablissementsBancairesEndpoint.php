<?php

namespace MonIndemnisationJustice\Api\Requerant\EtablissementBancaire;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\EtablissementBancaire;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Service\Banque\TelechargeurLogosEtablissementsBancaires;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Liste des établissements bancaires actifs, pour reconnaître la banque à partir du code banque d'un RIB ou d'un IBAN
 * français et en suggérer le nom (et le logo) au requérant.
 */
#[Route('/api/requerant/etablissements-bancaires', name: 'api_requerant_etablissements_bancaires_lister', methods: ['GET'])]
#[IsGranted(
    Usager::ROLE_REQUERANT,
    message: 'La liste des établissements bancaires est réservée aux usagers identifiés sur la plateforme',
    statusCode: Response::HTTP_FORBIDDEN
)]
class ListerEtablissementsBancairesEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $reponse = new JsonResponse(array_map(
            fn (EtablissementBancaire $etablissement) => [
                'codeBanque' => $etablissement->getCodeBanque(),
                'nom' => $etablissement->getNom(),
                'logo' => null !== $etablissement->getLogo()
                    ? TelechargeurLogosEtablissementsBancaires::DOSSIER_PUBLIC.'/'.$etablissement->getLogo()
                    : null,
            ],
            $this->em->getRepository(EtablissementBancaire::class)->findBy(['estActif' => true], ['codeBanque' => 'ASC'])
        ));

        // Référentiel quasi statique : le navigateur peut le garder en cache une heure
        return $reponse->setPrivate()->setMaxAge(3600);
    }
}
