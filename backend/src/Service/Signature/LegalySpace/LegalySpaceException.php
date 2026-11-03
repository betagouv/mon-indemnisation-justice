<?php

namespace MonIndemnisationJustice\Service\Signature\LegalySpace;

/**
 * Erreur renvoyée par l'API LegalySpace, dont le corps JSON porte un `code` métier (ex : `PROCESS_NOT_READY`,
 * `DOC_PROTECTED`), un `message` et, pour les erreurs de validation, des `details`.
 */
class LegalySpaceException extends \Exception
{
    public function __construct(
        string $message,
        public readonly int $statutHttp = 0,
        public readonly ?string $codeErreur = null,
        public readonly array $details = [],
    ) {
        parent::__construct($message, $statutHttp);
    }

    public static function depuisReponse(int $statutHttp, array $data): self
    {
        $codeErreur = isset($data['code']) ? (string) $data['code'] : null;
        $message = $data['message'] ?? "Une erreur est survenue lors de la communication avec l'API LegalySpace.";

        return new self(
            sprintf('[LegalySpace %d%s] %s', $statutHttp, $codeErreur ? " {$codeErreur}" : '', $message),
            $statutHttp,
            $codeErreur,
            (array) ($data['details'] ?? []),
        );
    }
}
