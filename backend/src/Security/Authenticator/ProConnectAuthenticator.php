<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\FournisseurIdentiteAgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
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
use Symfony\Component\Security\Http\HttpUtils;

class ProConnectAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        protected readonly HttpUtils $httpUtils,
        #[Autowire(service: 'oidc_client_pro_connect')]
        protected readonly OidcClient $oidcClient,
        protected readonly AgentRepository $agentRepository,
        protected readonly FournisseurIdentiteAgentRepository $fournisseurIdentiteAgentRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
        /** @var array $autoPromotionHashes liste de _hashes_ d'adresses courriel pour lesquelles le rôle
         * `ROLE_AGENT_GESTION_PERSONNEL` est automatiquement attribué
         */
        #[Autowire('%env(default::json:MIJ_AUTO_PROMOTION_HASHES)%')]
        protected readonly ?array $autoPromotionHashes,
    ) {}

    public function supports(Request $request): ?bool
    {
        return
            'agent_securite_connexion' === $request->attributes->get('_route')
            && $request->query->has('state')
            && (
                $request->query->has('code')
                || $request->query->has('error')
            );
    }

    public function authenticate(Request $request): Passport
    {
        try {
            // Authenticate
            list($accessToken) = $this->oidcClient->authenticate($request);

            // User info
            $userInfo = $this->oidcClient->fetchUserInfo($accessToken);
            $fournisseurIdentite = $this->fournisseurIdentiteAgentRepository->find($userInfo['idp_id']);
            $autoPromotion = in_array(sha1($userInfo['email']), $this->autoPromotionHashes ?? []);

            $agent = $this->agentRepository->findOneBy(['identifiant' => $userInfo['sub']]);

            if (null === $agent) {
                if (null === $fournisseurIdentite?->getAdministration() && !$autoPromotion) {
                    throw new AuthenticationException("Cet espace est réservé aux agents des Forces de l'ordre ou du Ministère de la Justice");
                }

                $agent = ($this->agentRepository->findOneBy(['email' => $userInfo['email']]) ?? new Agent())
                    ->setIdentifiant($userInfo['sub'])
                    ->setEmail($userInfo['email'])
                    ->setPrenom($userInfo['given_name'])
                    ->setNom($userInfo['usual_name'])
                    ->addRole(Agent::ROLE_AGENT)
                    ->setUid($userInfo['uid'])
                    ->setCree()
                    ->setFournisseurIdentite($fournisseurIdentite)
                    ->setDonnesAuthentification($userInfo)
                ;

                if ($autoPromotion) {
                    $agent
                        ->addRole(Agent::ROLE_AGENT_DOSSIER)
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

                if ($autoPromotion) {
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

            return new SelfValidatingPassport(new UserBadge($agent->getIdentifiant()));
        } catch (AuthenticationException $e) {
            $this->logger->error($e->getMessage(), $e->getMessageData());

            throw $e;
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Si une URL était ciblée avant connexion, alors on y redirige l'agent connecté
        if (!empty($redirection = $request->getSession()->remove('agent_connexion_redirection'))) {
            return new RedirectResponse($redirection);
        }

        // Sinon on renvoie vers la route d'accueil
        return new RedirectResponse($this->urlGenerator->generate('agent_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login', ['erreur' => $exception->getMessage()]));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $request->getSession()->set('agent_connexion_redirection', $request->getPathInfo());

        return new RedirectResponse(
            $this->urlGenerator->generate('app_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
