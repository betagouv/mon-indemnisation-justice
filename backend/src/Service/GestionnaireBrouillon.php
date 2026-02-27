<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GestionnaireBrouillon
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NormalizerInterface    $normalizer,
        protected readonly DenormalizerInterface  $denormalizer,
        protected readonly ObjectMapperInterface  $mapper,
        protected readonly ValidatorInterface     $validator,
    )
    {
    }

    public function initierDepuis(mixed $source, ?Requerant $requerant = null, ?Agent $agent = null): Brouillon
    {
        $type = BrouillonType::detecterDepuisSource($source);

        if (!$type) {
            throw new \Exception("Impossible de determiner le type de brouillon associé à un object de type '" . get_class($source) . "'");
        }

        return $this->initier($type, $requerant, $this->normalizer->normalize($source));
    }

    /**
     * Initier un brouillon de type $type.
     */
    public function initier(BrouillonType $type, ?Requerant $requerant = null, ?Agent $agent = null, ?array $donnees = []): Brouillon
    {
        $brouillon = new Brouillon()
            ->setType($type)
            ->setRequerant($requerant)
            ->setAgent($agent)
            ->setDonnees($donnees);

        $this->em->persist($brouillon);
        $this->em->flush();

        return $brouillon;
    }

    /**
     * Amender un brouillon avec des données, pour une requête de type `PATCH`.
     */
    public function amender(Brouillon $brouillon, array $donnees): Brouillon
    {
        $brouillon->patchDonnees($donnees);

        $this->em->persist($brouillon);
        $this->em->flush();

        return $brouillon;
    }

    /**
     * Vérifie que l'entité ciblée par le brouillon est valide en fournissant la liste des violations. Si celle-ci est
     * vide alors l'entité est valide.
     */
    public function verifier(Brouillon $brouillon): array
    {
        return $this->listerViolations($this->extraireEntiteTravail($brouillon));
    }

    /**
     * Extraie les violations à partir d'une instance, sous forme de tableau dont les clefs sont les noms des champs et
     * les valeurs sont les violations, récursivement.
     */
    protected function listerViolations(object $entite): array
    {
        $violations = $this->validator->validate($entite);

        return array_merge(
            ...array_map(
                fn($v) => [$v->getPropertyPath() => $v->getMessage()],
                iterator_to_array($violations->getIterator())
            )
        );
    }

    /**
     * Génère l'entité cible du brouillon à partir de ses données brutes.
     *
     * @return object|mixed|string
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function extraireEntiteTravail(Brouillon $brouillon): object
    {
        return $this->denormalizer->denormalize(
            $brouillon->getDonnees(),
            $brouillon->getType()->getClasseTravail(),
            context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]
        );
    }

    /**
     * Publie le brouillon en le supprimant au profit de l'entité cible qu'il a engendré.
     *
     * @return object|mixed|string
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function publier(Brouillon $brouillon): object
    {
        $travail = $this->extraireEntiteTravail($brouillon);
        if (!empty($violations = $this->listerViolations($travail))) {
            throw new \Exception('Impossible de publier le brouillon de type ' . strtolower($brouillon->getType()->getLibelle()));
        }

        $entite = $this->mapper->map($travail, $brouillon->getType()->getClassePublication());

        $this->em->persist($entite);
        $this->em->remove($brouillon);

        $this->em->flush();

        return $entite;
    }
}
