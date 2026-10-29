<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Request\Attribute;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Dto\DeclarationFDOBrisPorteDto;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Uid\Uuid;

class MapDeclarationFDOResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        foreach ($argument->getAttributes(MapDeclarationFDODto::class) as $attribut) {
            yield $this->getDto($request, $this->getDeclaration($request, $attribut->id));
        }

        foreach ($argument->getAttributes(MapDeclarationFDO::class) as $attribut) {
            $declaration = $this->getDeclaration($request, $attribut->id);

            if ($attribut->modifie) {
                $dto = $this->getDto($request, $declaration);
                $this->verifierModifications($declaration, $dto);

                yield $dto->versDeclaration($declaration);
            } else {
                yield $declaration;
            }
        }
    }

    /**
     * Vérifie que les modifications portées par le DTO sont autorisées compte tenu de l'état de la déclaration.
     */
    protected function verifierModifications(DeclarationFDOBrisPorte $declaration, DeclarationFDOBrisPorteDto $dto): void
    {
        // Une déclaration déjà soumise ne peut pas perdre les coordonnées du requérant
        if (!$declaration->estBrouillon() && null !== $declaration->getCoordonneesRequerant() && null === $dto->coordonneesRequerant) {
            throw new BadRequestHttpException("Les coordonnées du requérant d'une déclaration déjà soumise ne peuvent pas être supprimées");
        }
    }

    public function getDto(Request $request, DeclarationFDOBrisPorte $declaration): DeclarationFDOBrisPorteDto
    {
        // Mapper l'entité déclaration source sur un DTO
        $dto = DeclarationFDOBrisPorteDto::depuisDeclaration($declaration);

        // En cas de requête PATCH ...
        if (Request::METHOD_PATCH === $request->getMethod()) {
            $charge = json_decode($request->getContent(), true);
            // ... avec une charge utile non vide...
            if (is_array($charge) && !empty($charge)) {
                // ... on enrichit le DTO avec les valeurs fournies (les champs `readonly` du DTO ne pouvant être modifiés)
                try {
                    $dto = $this->denormalizer->denormalize(
                        $charge,
                        DeclarationFDOBrisPorteDto::class,
                        context: [
                            AbstractNormalizer::OBJECT_TO_POPULATE => $dto,
                            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                        ]
                    );
                } catch (ExtraAttributesException $e) {
                    throw new BadRequestHttpException((count($e->getExtraAttributes()) > 1 ? 'Champs non reconnus' : 'Champ non reconnu').': '.implode(', ', $e->getExtraAttributes()), $e);
                } catch (UnexpectedValueException $e) {
                    throw new BadRequestHttpException($e->getMessage(), $e);
                }
            }
        }

        return $dto;
    }

    protected function getDeclaration(Request $request, string $id): DeclarationFDOBrisPorte
    {
        $id = $request->attributes->get($id);
        $declaration = is_string($id) && Uuid::isValid($id) ? $this->em->find(DeclarationFDOBrisPorte::class, $id) : null;

        if (null === $declaration) {
            throw new NotFoundHttpException('Déclaration inconnue');
        }

        return $declaration;
    }
}
