<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DemandeDysfonctionnementDto;
use MonIndemnisationJustice\Api\Requerant\Request\Attribute\MapDossier;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// `MapDossierDto` (contrairement à `MapDossier`) est câblé en dur sur `DossierDto::class` et n'est donc pas
// réutilisable ici : la fusion PATCH → DTO ci-dessous reproduit à la main ce que fait `MapDossierResolver::getDto()`.
#[Route('/api/requerant/dossier/dysfonctionnement/{reference}/amender', name: 'api_requerant_dossier_dysfonctionnement_amender', methods: ['PATCH'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_AMENDER, 'dossier', message: 'Seul le requérant peut amender son dossier', statusCode: Response::HTTP_FORBIDDEN)]
class AmenderDossierDysfonctionnementEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function __invoke(
        #[MapDossier(modifie: false, type: DossierType::DYSFONCTIONNEMENT)]
        Dossier $dossier,
        Request $request,
    ) {
        $dto = DemandeDysfonctionnementDto::depuisDossier($dossier);

        $charge = json_decode($request->getContent(), true);
        if (is_array($charge) && !empty($charge)) {
            $dto = $this->denormalizer->denormalize(
                $charge,
                DemandeDysfonctionnementDto::class,
                context: [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $dto,
                    AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                ]
            );
        }

        $dossier = $dto->versDossier($dossier);
        $this->em->persist($dossier);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(DemandeDysfonctionnementDto::depuisDossier($dossier), 'json'),
            Response::HTTP_OK
        );
    }
}
