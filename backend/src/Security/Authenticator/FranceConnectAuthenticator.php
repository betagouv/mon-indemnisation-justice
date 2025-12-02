<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Repository\RequerantRepository;
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
use Symfony\Component\Security\Http\HttpUtils;

class FranceConnectAuthenticator extends AbstractAuthenticator
{
    public const LOGOUT_URL_SESSION_KEY = 'france_connect_deconnexion_url';

    public function __construct(
        protected readonly HttpUtils $httpUtils,
        protected readonly string $loginPageRoute,
        protected readonly string $signupCheckRoute,
        protected readonly string $loginCheckRoute,
        protected readonly string $loginSuccessRoute,
        #[Autowire(service: 'oidc_client_france_connect')]
        protected readonly OidcClient $oidcClient,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
        protected readonly RequerantRepository $requerantRepository,
    ) {}

    public function supports(Request $request): ?bool
    {
        return
            (
                $this->httpUtils->checkRequestPath($request, $this->signupCheckRoute)
                || $this->httpUtils->checkRequestPath($request, $this->loginCheckRoute)
            )
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
            list($accessToken, $idToken) = $this->oidcClient->authenticate($request);

            // User info
            $userInfo = $this->oidcClient->fetchUserInfo($accessToken);

            $requerant = $this->requerantRepository->findByEmailOrSub($userInfo['email'] ?? null, $userInfo['sub'] ?? null);
            if (null === $requerant) {
                if ($this->httpUtils->checkRequestPath($request, $this->signupCheckRoute)) {
                    // Inscription
                    $prenoms = $userInfo['given_name_array'] ?? explode(' ', $userInfo['given_name']);

                    $requerant = (new Requerant())
                        ->setSub($userInfo['sub'])
                        ->setEmail($userInfo['email'] ?? null)
                        ->setVerifieCourriel()
                        ->setPersonnePhysique(
                            (new PersonnePhysique())
                                ->setCivilite('male' === $userInfo['gender'] ? Civilite::M : Civilite::MME)
                                ->setNom($userInfo['family_name'] ?? '')
                                ->setNomNaissance($userInfo['family_name'] ?? '')
                                ->setPrenom1($prenoms[0] ?? null)
                                ->setPrenom2($prenoms[1] ?? null)
                                ->setPrenom3($prenoms[2] ?? null)
                                ->setDateNaissance(new \DateTime($userInfo['birthdate'] ?? ''))
                                ->setEmail($userInfo['email'] ?? null)
                        )
                    ;

                    // Récupération du pays de naissance
                    if (null !== ($codePaysNaissance = $userInfo['birthcountry'])) {
                        /** @var GeoPays $paysNaissance */
                        $paysNaissance = $this->em->getRepository(GeoPays::class)->findOneBy(
                            [
                                'codeInsee' => $codePaysNaissance]
                        );

                        if (null !== $paysNaissance) {
                            $requerant->getPersonnePhysique()->setPaysNaissance($paysNaissance);
                        }
                    }

                    // Récupération de la commune de naissance
                    if (null !== ($codeCommuneNaissance = $userInfo['birthplace'])) {
                        /** @var GeoCodePostal $codePostalNaissance */
                        $codePostalNaissance = $this->em->getRepository(GeoCodePostal::class)->identifier($codeCommuneNaissance);

                        if (null !== $codePostalNaissance) {
                            $requerant->getPersonnePhysique()
                                ->setCommuneNaissance($codePostalNaissance)
                                ->setPaysNaissance($this->em->getRepository(GeoPays::class)->getFrance())
                            ;
                        }
                    }

                    $this->em->persist($requerant);
                    $this->em->flush();
                } else {
                    // Connexion
                    throw new AuthenticationException('Utilisateur non reconnu');
                }
            }

            $request->getSession()->set(self::LOGOUT_URL_SESSION_KEY, $this->oidcClient->buildLogoutUrl($request, $idToken));

            return new SelfValidatingPassport(new UserBadge($requerant->getUserIdentifier()));
        } catch (AuthenticationException $e) {
            $this->logger->error($e->getMessage(), $e->getMessageData());

            throw $e;
        }
    }

    public function getUrlDeconnexion(Request $request): ?string
    {
        return $request->getSession()->get(self::LOGOUT_URL_SESSION_KEY);
    }

    public function logout(Request $request): void
    {
        if ($this->oidcClient->logout($request)) {
            $request->getSession()->remove(self::LOGOUT_URL_SESSION_KEY);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginSuccessRoute));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate($this->loginPageRoute));
    }
}
