<?php

namespace MonIndemnisationJustice\Service;

use Random\RandomException;
use Sqids\Sqids;

/**
 * Service générant des courtes références, type code de réservation.
 */
class GenerateurReferenceCourte
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    protected readonly Sqids $generateur;

    public function __construct()
    {
        $this->generateur = new Sqids(alphabet: self::ALPHABET, minLength: 6);
    }

    /**
     * @param array<int> $ids
     *
     * @throws RandomException
     */
    public function generer(int $longueur = 6, ?array $ids = null): string
    {
        $reference = $this->generateur->encode($ids ?? [random_int(1, PHP_INT_MAX)]);

        return substr($reference, 0, $longueur);
    }

    /**
     * @throws RandomException
     */
    public function genererJusque(callable $verification, ?array $ids = null, int $longueur = 6, int $nbTentatives = 3): string
    {
        $essais = 0;
        do {
            $reference = $this->generer($longueur, $ids);
        } while (++$essais <= $nbTentatives && $verification($reference));

        return $reference;
    }
}
