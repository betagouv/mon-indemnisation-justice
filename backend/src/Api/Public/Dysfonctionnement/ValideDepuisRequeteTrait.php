<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Désérialise le corps JSON d'une requête en un DTO donné puis le valide, avec le contrat d'erreurs
 * `{propertyPath: message}` attendu côté frontend (cf. AuthentificationService.ts, `poster()`) — distinct
 * du format `{erreur: "..."}` de l'ApiExceptionListener global, qui ne s'applique pas à ces routes puisqu'elles
 * répondent elles-mêmes avant qu'une exception ne soit levée.
 *
 * Partagé entre InscrireUsagerEndpoint et InscrireAvocatEndpoint, seuls consommateurs de ce contrat pour l'instant.
 */
trait ValideDepuisRequeteTrait
{
    /**
     * @template T of object
     *
     * @param class-string<T> $classe
     *
     * @return T|JsonResponse le DTO validé, ou une réponse 400 prête à retourner s'il y a des violations
     */
    private function deserialiserEtValider(
        Request $request,
        string $classe,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): mixed {
        $input = $serializer->deserialize($request->getContent(), $classe, 'json');

        $violations = $validator->validate($input);

        if ($violations->count() > 0) {
            return new JsonResponse([
                'erreurs' => array_merge(
                    ...array_map(
                        fn ($v) => [$v->getPropertyPath() => $v->getMessage()],
                        iterator_to_array($violations->getIterator())
                    )
                ),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $input;
    }
}
