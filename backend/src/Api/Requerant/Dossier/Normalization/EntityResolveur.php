<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Normalization;

use Doctrine\ORM\EntityManagerInterface;

class EntityResolveur
{
    private static ?EntityManagerInterface $em = null;

    public static function configurer(EntityManagerInterface $em): void
    {
        self::$em = $em;
    }

    public static function resoudre(string $class, mixed $id): ?object
    {
        if (null === self::$em) {
            throw new \RuntimeException("Impossible de résoudre l'entité $class d'id $id : la connexion n'est pas initiée");
        }

        return self::$em->getRepository($class)->find($id);
    }

    public static function resoudreListe(string $class, array $ids): array
    {
        if (null === self::$em) {
            throw new \RuntimeException("Impossible de résoudre la liste d'entités $class d'id : la connexion n'est pas initiée");
        }

        return sizeof($ids) > 0 ? self::$em->getRepository($class)->findBy($ids) : [];
    }
}
