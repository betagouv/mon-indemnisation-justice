<?php

namespace MonIndemnisationJustice\Api\Requerant\Request\Attribute;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MapDossierResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        foreach ($this->getDossierDtos($request, ...$argument->getAttributes(MapDossierDto::class)) as $dto) {
            yield $dto;
        }

        foreach ($this->getDossiers($request, ...$argument->getAttributes(MapDossier::class)) as $dossier) {
            yield $dossier;
        }
    }

    public function getDto(Request $request, Dossier $dossier): DossierDto
    {
        // Mapper l'entité dossier source sur un DTO
        $dto = DossierDto::depuisDossier($dossier);

        // En cas de requête PATCH ...
        if (Request::METHOD_PATCH === $request->getMethod()) {
            $charge = json_decode($request->getContent(), true);
            // ... avec une charge utile non vide...
            if (is_array($charge) && !empty($charge)) {
                // ... on enrichit le DTO avec les valeurs fournies
                $dto = $this->denormalizer->denormalize(
                    $charge,
                    DossierDto::class,
                    context: [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $dto,
                        AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                        AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                    ]
                );
            }
        }

        return $dto;
    }

    protected function getDossier(string $reference, DossierType $type): ?Dossier
    {
        return $this->em->getRepository(Dossier::class)->getByIdOuReference($reference, $type);
    }

    /**
     * @param MapDossierDto[] $attributs
     *
     * @return iterable<DossierDto>
     */
    public function getDossierDtos(Request $request, ...$attributs): iterable
    {
        foreach ($attributs as $attribut) {
            $reference = $request->attributes->get($attribut->reference);
            if (empty($reference)) {
                return [];
            }

            $dossier = $this->getDossier($reference, $attribut->type);

            if (null === $dossier) {
                throw new NotFoundHttpException('Aucun dossier trouvé');
            }

            yield $this->getDto($request, $dossier);
        }
    }

    /**
     * @param MapDossier[] $attributs
     *
     * @return iterable<Dossier>
     */
    public function getDossiers(Request $request, ...$attributs): iterable
    {
        foreach ($attributs as $attribut) {
            $reference = $request->attributes->get($attribut->reference);
            if (empty($reference)) {
                return [];
            }

            $dossier = $this->getDossier($reference, $attribut->type);

            if ($attribut->modifie) {
                $dto = $this->getDto($request, $dossier);
                $dossier = $dto->versDossier($dossier);
            }

            yield $dossier;
        }
    }
}
