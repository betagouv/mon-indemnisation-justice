<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use MonIndemnisationJustice\Entity\AdministrationType;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AdministrationRepository;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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
        protected readonly AdministrationRepository $administrationRepository,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
        /** @var array $autoPromotionHashes liste de _hashes_ d'adresses courriel pour lesquelles le rôle
         * `ROLE_AGENT_GESTION_PERSONNEL` est automatiquement attribué
         */
        #[Autowire('%env(default::json:MIJ_AUTO_PROMOTION_HASHES)%')]
        protected readonly ?array $autoPromotionHashes,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            'securite_agent_connexion' === $request->attributes->get('_route')
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
            // Cas spécifiques aux agents de l'équipe Beta : bien que liés à une autre administration (DINUM https://annuaire-entreprises.data.gouv.fr/etablissement/13002526500013)
            // si le sha1 de l'adresse courriel apparaît dans les listes des _hashes_ à auto-promouvoir, on les affecte
            // directement au Ministère de la Justice
            $estAutoPromuMJ = in_array(sha1($userInfo['email']), $this->autoPromotionHashes ?? []);
            $administration = $estAutoPromuMJ ? $this->administrationRepository->find(AdministrationType::MINISTERE_JUSTICE) : $this->administrationRepository->findBySiret($userInfo['siret']);

            if (null === $administration) {
                throw new CustomUserMessageAuthenticationException("Cet espace est réservé aux agents des Forces de l'ordre ou du Ministère de la Justice");
            }

            $agent = $this->agentRepository->findOneBy(['identifiant' => $userInfo['sub']]);

            if (null === $agent) {
                // Création de compte
                $agent = ($this->agentRepository->findOneBy(['email' => $userInfo['email']]) ?? new Agent())
                    ->setIdentifiant($userInfo['sub'])
                    ->setEmail($userInfo['email'])
                    ->setPrenom($userInfo['given_name'])
                    ->setNom($userInfo['usual_name'])
                    ->setAdministration($administration)
                    ->addRole(Agent::ROLE_AGENT)
                    ->setUid($userInfo['uid'])
                    ->setCree()
                    ->setDonneesAuthentification($userInfo);

                if ($estAutoPromuMJ) {
                    $agent
                        ->addRole(Agent::ROLE_AGENT_DOSSIER)
                        ->addRole(Agent::ROLE_AGENT_GESTION_PERSONNEL)
                        ->addRole(Agent::ROLE_AGENT_REDACTEUR)
                        ->addRole(Agent::ROLE_AGENT_BETAGOUV)
                        ->setValide();
                }
            } else {
                // Connexion à un compte déjà existant
                $agent
                    ->setAdministration($administration)
                    ->setEmail($userInfo['email'])
                    ->setPrenom($userInfo['given_name'])
                    ->setNom($userInfo['usual_name']);

                if ($estAutoPromuMJ) {
                    $agent->addRole(Agent::ROLE_AGENT_BETAGOUV);
                }

                // Rattrapage des donées 'custom' pour les agents connectés avant l'intégration de ces données
                // supplémentaires https://partenaires.proconnect.gouv.fr/docs/fournisseur-service/custom-scope
                if (
                    isset($userInfo['custom'])
                    && null !== ($donneesAuthentification = $agent->getDonneesAuthentification())
                    && !isset($donneesAuthentification['custom'])
                ) {
                    $agent->setDonneesAuthentification(
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
        $request->getSession()->getFlashBag()->add('erreur_identification', $exception->getMessage());

        return new RedirectResponse($this->urlGenerator->generate('securite_connexion'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $request->getSession()->set('agent_connexion_redirection', $request->getPathInfo());

        return new RedirectResponse(
            $this->urlGenerator->generate('securite_connexion'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
