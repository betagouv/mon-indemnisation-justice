<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{reference}/amender', name: 'api_requerant_dossier_bris_porte_amender', methods: ['PATCH'])]
class AmenderDossierEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface  $mapper,
        protected readonly DenormalizerInterface  $denormalizer,
        protected readonly NormalizerInterface    $normalizer,
    )
    {
    }

    public function __invoke(
        string  $reference,
        Request $request,
    )
    {
        // TODO créer un attribut
        /* @var Dossier $dossier */
        $dossier = $this->em->getRepository(Dossier::class)->getByIdOuReference($reference, DossierType::BRIS_PORTE);

        $dto = DossierDto::depuisDossier($dossier);

        $dto = $this->denormalizer->denormalize(
            json_decode($request->getContent(), true),
            DossierDto::class,
            context: [
                AbstractNormalizer::OBJECT_TO_POPULATE => $dto,
                //AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            ]
        );

        $dossier = $dto->versDossier($dossier);

        $this->em->persist($dossier);
        $this->em->flush();


        return new JsonResponse(
            $this->normalizer->normalize($dto, 'json'),
            Response::HTTP_OK
        );
    }
}
