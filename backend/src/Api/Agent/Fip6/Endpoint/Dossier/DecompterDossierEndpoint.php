<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui décompte le nombre de dossiers par type d'action à réaliser pour l'agent connecté (dossier à instruire,
 * PI à signer, à transmettre à FIP3, etc.).
 */
#[Route('/api/agent/fip6/decompter-dossiers', name: 'api_agent_agents_decompter_dossiers', methods: ['GET'])]
#[IsGranted(
    DossierVoter::ACTION_DECOMPTER,
    message: 'Le décompte des dossiers est restreint aux agents du Bureau du précontentieux',
    statusCode: Response::HTTP_FORBIDDEN
)]
class DecompterDossierEndpoint
{
    public function __invoke(Security $security, DossierRepository $dossierRepository): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $decomptes = [];

        if ($agent->aRole(Agent::ROLE_AGENT_BETAGOUV)) {
            $decomptes['a-categoriser'] = $dossierRepository->compterDossierACategoriser();
        }

        if ($agent->aRole(Agent::ROLE_AGENT_ATTRIBUTEUR)) {
            $decomptes['a-attribuer'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER);
        }

        if ($agent->estRedacteur()) {
            $decomptes['a-instruire'] = $agent->nbDossiersAInstruire();
            $decomptes['en-instruction'] = $agent->nbDossiersEnInstruction();
            $decomptes['a-verifier'] = $agent->nbDossiersAVerifier();
        }

        if ($agent->aRole(Agent::ROLE_AGENT_VALIDATEUR)) {
            $decomptes['rejet-a-signer'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_KO_A_SIGNER);
            $decomptes['proposition-a-signer'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);
            $decomptes['arrete-a-signer'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_OK_VERIFIE);
        }

        if ($agent->aRole(Agent::ROLE_AGENT_LIAISON_BUDGET) || $agent->estRedacteur()) {
            if ($agent->aRole(Agent::ROLE_AGENT_LIAISON_BUDGET)) {

                $decomptes['a-transmettre'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_OK_A_INDEMNISER);
                $decomptes['en-attente-indemnisation'] = $dossierRepository->compterDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);
            } else {
                $decomptes['a-transmettre'] = $agent->nbDossiersATransmettreAFIP3();
                $decomptes['en-attente-indemnisation'] = $agent->nbDossiersEnAttentePaiement();
            }
        }

        return new JsonResponse($decomptes);
    }
}
