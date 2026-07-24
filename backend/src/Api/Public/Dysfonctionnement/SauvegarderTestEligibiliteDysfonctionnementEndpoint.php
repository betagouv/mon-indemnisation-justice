<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\TestEligibiliteDysfonctionnementInput;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/public/dysfonctionnement/test-eligibilite', name: 'api_public_dysfonctionnement_test_eligibilite', methods: ['PUT'], env: ['dev', 'test', 'ci', 'develop'])]
class SauvegarderTestEligibiliteDysfonctionnementEndpoint
{
    public const CLEF_SESSION = 'dysfonctionnement_test_eligibilite';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload]
        TestEligibiliteDysfonctionnementInput $input,
        Request $request,
    ): Response {
        $session = $request->getSession();

        $test = null;
        $testId = $session->get(self::CLEF_SESSION);
        if ($testId) {
            $test = $this->em->getRepository(TestEligibiliteDysfonctionnement::class)->find($testId);
        }

        $test = $input->versTestEligibiliteDysfonctionnement($test);

        $this->em->persist($test);
        $this->em->flush();

        $session->set(self::CLEF_SESSION, $test->id);

        // TODO : rattacher le test à l'usager lors de la création de compte

        return new JsonResponse($this->normalizer->normalize([
            'id' => $test->id,
            'estEligible' => $test->estEligible(),
        ], 'json'));
    }
}
