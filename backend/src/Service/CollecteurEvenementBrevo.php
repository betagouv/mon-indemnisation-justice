<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\EnvoiCourriel;
use MonIndemnisationJustice\Entity\EnvoiCourrielEvenement;
use MonIndemnisationJustice\Entity\EnvoiCourrielEvenementType;
use MonIndemnisationJustice\Repository\EnvoiCourrielRepository;
use MonIndemnisationJustice\Service\Brevo\TransactionalWebhookPayloadDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Collecte des évènements d'email depuis Brevo.
 */
class CollecteurEvenementBrevo
{
    public function __construct(
        #[Autowire(env: 'default::BREVO_TOKEN_VERIFICATION')]
        protected readonly ?string $tokenVerification = null,
        protected readonly EnvoiCourrielRepository $envoiCourrielRepository,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly ValidatorInterface $validator,
    ) {

    }

    public function estActif(): bool
    {
        return null !== $this->tokenVerification;
    }

    public function genererEnTetesCourriel(EnvoiCourriel $envoiCourriel): array
    {
        if ($this->estActif()) {
            return [
                // Le seul header personnalisé toléré par Brevo est `X-Mailin-Custom` https://community.brevo.com/t/custom-headers-in-webhooks/2125
                'X-Mailin-Custom' => $envoiCourriel->getId()->toString(),
            ];
        }

        return [];
    }

    public function collecter(Request $request): ?EnvoiCourrielEvenement
    {
        if (
            Request::METHOD_POST === $request->getMethod()
        ) {
            $token = preg_replace('/^Bearer\s+/i', '', $request->headers->get('Authorization', ''));
            if (hash_equals($token, $this->tokenVerification)) {
                $charge = $this->extraireDonneesEvenements($request);

                // Si les données sont valides…
                if (0 === $this->validator->validate($charge)->count()) {
                    // ... que le type d'évènement nous intéresse…
                    if (null !== ($type = self::detecterTypeEvenement($charge->event))) {
                        // ... et qu'on reconnaît le courriel suivi...
                        if (null !== ($envoiCourriel = $this->envoiCourrielRepository->find($charge->idCourriel))) {
                            // ... alors, on sauvegarde l'évènement
                            $envoiCourriel->ajouterEvenement(
                                new EnvoiCourrielEvenement()
                                    ->setType($type)
                                    ->setCourriel($envoiCourriel)
                                    ->setDate($charge->tsEvent)
                                    ->setContexte(
                                        array_merge(
                                            ['event' => $charge->event],
                                            $charge->userAgent ? ['user_agent' => $charge->userAgent] : [],
                                            $charge->deviceUsed ? ['device_used' => $charge->deviceUsed] : [],
                                        )
                                    )
                            );
                            $this->envoiCourrielRepository->save($envoiCourriel);
                        }
                    }
                }
            }
        }


        return null;
    }

    public function extraireDonneesEvenements(Request $request): ?TransactionalWebhookPayloadDto
    {
        return $this->denormalizer->denormalize(
            json_decode($request->getContent(), true),
            TransactionalWebhookPayloadDto::class,
        );
    }

    public static function detecterTypeEvenement(string $evenement): ?EnvoiCourrielEvenementType
    {
        return match (strtolower($evenement)) {
            // https://developers.brevo.com/docs/transactional-webhooks#sent
            'request' => EnvoiCourrielEvenementType::EXPEDIE,
            // https://developers.brevo.com/docs/transactional-webhooks#delivered
            'delivered', 'spam' => EnvoiCourrielEvenementType::RECU,
            'soft_bounce', 'hard_bounce', 'blocked', 'error' => EnvoiCourrielEvenementType::REJETE,
            // https://developers.brevo.com/docs/transactional-webhooks#opened
            'opened' => EnvoiCourrielEvenementType::OUVERT,

            'clic' => EnvoiCourrielEvenementType::CLIQUE,
        };
    }
}
