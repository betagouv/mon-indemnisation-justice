<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\FournisseurIdentiteAgentRepository;
use MonIndemnisationJustice\Security\Oidc\ProConnectClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ProConnectAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        protected readonly ProConnectClient $client,
        protected readonly AgentRepository $agentRepository,
        protected readonly FournisseurIdentiteAgentRepository $fournisseurIdentiteAgentRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
        /* @var array $autoPromotionHashes liste de _hashes_ d'adresses courriel pour lesquelles le rôle
         * `ROLE_AGENT_GESTION_PERSONNEL` est automatiquement attribué
         */
        #[Autowire('%env(default::json:MIJ_AUTO_PROMOTION_HASHES)%')]
        protected readonly mixed $autoPromotionHashes = [],
    ) {}

    public function supports(Request $request): ?bool
    {
        return 'agent_securite_connexion' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->client->getAccessToken();

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken) {
                $user = $this->client->fetchUserFromToken($accessToken);
                $userInfo = $user->toArray();

                $agent = $this->agentRepository->findOneBy(['identifiant' => $userInfo['sub']]);

                if (null === $agent) {
                    $agent = ($this->agentRepository->findOneBy(['email' => $userInfo['email']]) ?? new Agent())
                        ->setIdentifiant($userInfo['sub'])
                        ->setEmail($userInfo['email'])
                        ->setPrenom($userInfo['given_name'])
                        ->setNom($userInfo['usual_name'])
                        ->addRole(Agent::ROLE_AGENT)
                        ->setUid($userInfo['uid'])
                        ->setCree()
                        ->setFournisseurIdentite($this->fournisseurIdentiteAgentRepository->find($userInfo['idp_id']))
                        ->setDonnesAuthentification($userInfo)
                    ;

                    if (in_array(sha1($agent->getEmail()), $this->autoPromotionHashes ?? [])) {
                        $agent
                            ->addRole(Agent::ROLE_AGENT_GESTION_PERSONNEL)
                            ->addRole(Agent::ROLE_AGENT_REDACTEUR)
                            ->addRole(Agent::ROLE_AGENT_BETAGOUV)
                            ->setAdministration(Administration::MINISTERE_JUSTICE)
                            ->setValide()
                        ;
                    }
                } else {
                    $agent->setEmail($userInfo['email'])
                        ->setPrenom($userInfo['given_name'])
                        ->setNom($userInfo['usual_name'])
                    ;

                    if (in_array(sha1($agent->getEmail()), $this->autoPromotionHashes ?? [])) {
                        $agent->addRole(Agent::ROLE_AGENT_BETAGOUV);
                    }

                    // Rattrapage des donées 'custom' pour les agents connectés avant l'intégration de ces données
                    // supplémentaires https://partenaires.proconnect.gouv.fr/docs/fournisseur-service/custom-scope
                    if (
                        isset($userInfo['custom'])
                        && null !== ($donneesAuthentification = $agent->getDonnesAuthentification())
                        && !isset($donneesAuthentification['custom'])
                    ) {
                        $agent->setDonnesAuthentification(
                            array_merge(
                                $donneesAuthentification,
                                $userInfo
                            )
                        );
                    }
                }

                $this->agentRepository->save($agent);

                return $agent;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($request->getSession()->remove('agent_connexion_redirection') ?? $this->urlGenerator->generate('agent_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login', ['erreur' => $exception->getMessage()]));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $request->getSession()->set('agent_connexion_redirection', $request->getBaseUrl());

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
