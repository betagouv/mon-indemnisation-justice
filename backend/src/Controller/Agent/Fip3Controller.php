<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/fip3')]
#[IsGranted(Agent::ROLE_AGENT)]
class Fip3Controller extends AbstractController
{
    #[Route('/{extra?}', name: 'agent_fip3_react', requirements: ['extra' => '.*'])]
    public function react(): Response
    {
        /** @var Agent $agent */
        $agent = $this->getUser();

        return $this->render(
            'agent/fip3.html.twig',
            [
                'react' => [
                    'agent' => [
                        'id' => $agent->getId(),
                        'prenom' => $agent->getPrenom(),
                        'nom' => $agent->getNom(),
                        'permissions' => array_map(
                            fn (string $role) => preg_replace('/^ROLE_AGENT_/', '', $role),
                            array_values(
                                array_filter(
                                    $agent->getRoles(),
                                    fn (string $role) => in_array(
                                        $role,
                                        [
                                            Agent::ROLE_AGENT_DOSSIER,
                                            Agent::ROLE_AGENT_REDACTEUR,
                                            Agent::ROLE_AGENT_GESTION_PERSONNEL,
                                            Agent::ROLE_AGENT_ATTRIBUTEUR,
                                            Agent::ROLE_AGENT_VALIDATEUR,
                                            Agent::ROLE_AGENT_LIAISON_BUDGET,
                                            Agent::ROLE_AGENT_BETAGOUV,
                                        ]
                                    )
                                )
                            )
                        ),
                    ],
                ],
            ]
        );
    }
}
