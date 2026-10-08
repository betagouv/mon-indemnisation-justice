<?php

namespace MonIndemnisationJustice\Security\Authenticator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Controller\BrisPorteController;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Repository\UsagerRepository;
use MonIndemnisationJustice\Service\ConstructeurUsagerDepuisDeclaration;
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
        protected readonly UsagerRepository $usagerRepository,
        protected readonly ConstructeurUsagerDepuisDeclaration $constructeurUsagerDepuisDeclaration,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return
            $request->isMethod(Request::METHOD_GET)
            && (
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

            // User info (doc: https://docs.partenaires.franceconnect.gouv.fr/fs/fs-technique/fs-technique-scope-fc/#liste-des-claims)
            $userInfo = $this->oidcClient->fetchUserInfo($accessToken);
            $courriel = strtolower($userInfo['email'] ?? '');

            // Venue d'une invitation : l'identité FranceConnect doit correspondre à celle de la déclaration
            $referenceInvitation = $request->getSession()->get(BrisPorteController::CLEF_SESSION_INVITATION_FRANCE_CONNECT);
            $declarationInvitation = null;

            if (null !== $referenceInvitation) {
                $declarationInvitation = $this->em->getRepository(DeclarationFDOBrisPorte::class)->findOneBy(['reference' => $referenceInvitation]);
                $coordonneesRequerant = $declarationInvitation?->getCoordonneesRequerant();

                if (null === $declarationInvitation || $declarationInvitation->estAttribue() || null === $coordonneesRequerant) {
                    throw new CustomUserMessageAuthenticationException("Ce lien d'invitation n'est plus valide.");
                }

                if (strtolower($coordonneesRequerant->getCourriel()) !== $courriel) {
                    throw new CustomUserMessageAuthenticationException("L'identité FranceConnect ne correspond pas à celle de l'invitation. Utilisez le compte FranceConnect associé à l'adresse {$coordonneesRequerant->getCourriel()}.");
                }
            }

            $usager = $this->usagerRepository->findByEmailOrSub($courriel, $userInfo['sub'] ?? null);

            if (null === $usager) {
                if (null !== $declarationInvitation) {
                    $usager = $this->constructeurUsagerDepuisDeclaration->construire($declarationInvitation)
                        ->setSub($userInfo['sub'])
                        ->setVerifieCourriel();

                    $this->em->persist($usager);
                    $this->em->flush();
                } elseif ($this->httpUtils->checkRequestPath($request, $this->signupCheckRoute)) {
                    // Inscription
                    $prenoms = $userInfo['given_name_array'] ?? explode(' ', $userInfo['given_name']);

                    /** @var GeoPays $paysNaissance */
                    $paysNaissance = null !== ($codePaysNaissance = $userInfo['birthcountry']) ? $paysNaissance = $this->em->getRepository(GeoPays::class)->findOneBy(
                        [
                            'codeInsee' => $codePaysNaissance]
                    ) : null;

                    /** @var GeoCodePostal $codePostalNaissance */
                    $codePostalNaissance = null !== ($codeCommuneNaissance = $userInfo['birthplace']) ? $this->em->getRepository(GeoCodePostal::class)->identifier($codeCommuneNaissance) : null;

                    $usager = new Usager()
                        ->setSub($userInfo['sub'])
                        ->setEmail($courriel)
                        ->setVerifieCourriel()
                        ->setPersonne(
                            new Personne()
                                ->setCivilite('male' === $userInfo['gender'] ? Civilite::M : Civilite::MME)
                                ->setNom($userInfo['preferred_username'] ?? $userInfo['family_name'] ?? '')
                                ->setNomNaissance($userInfo['family_name'] ?? '')
                                ->setPrenom($prenoms[0] ?? null)
                                ->setCourriel($courriel)
                                ->ajouterPersonnePhysique(
                                    new PersonnePhysique()
                                        ->setPrenom2($prenoms[1] ?? null)
                                        ->setPrenom3($prenoms[2] ?? null)
                                        ->setDateNaissance(($dateNaissance = \DateTimeImmutable::createFromFormat('Y-m-d', $userInfo['birthdate'])) ? $dateNaissance : null)
                                        ->setPaysNaissance($paysNaissance)
                                        ->setCommuneNaissance($codePostalNaissance)
                                )
                        );


                    $this->em->persist($usager);
                    $this->em->flush();
                } else {
                    throw new CustomUserMessageAuthenticationException("Nous n'avons trouvé aucun compte enregistré sur notre plateforme depuis cet identifiant France Connect. Veuillez vous inscrire au préalable.", $userInfo);
                }
            }

            // On prépare l'URL de déconnexion à partir du token ID
            $request->getSession()->set(self::LOGOUT_URL_SESSION_KEY, $this->oidcClient->buildLogoutUrl($request, $idToken));

            return new SelfValidatingPassport(new UserBadge($usager->getUserIdentifier()));
        } catch (AuthenticationException $e) {
            $this->logger->error($e->getMessage(), $e->getMessageData());

            throw new CustomUserMessageAuthenticationException($e->getMessage());
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
        $request->getSession()->remove(BrisPorteController::CLEF_SESSION_INVITATION_FRANCE_CONNECT);

        return new RedirectResponse($this->urlGenerator->generate($this->loginSuccessRoute));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('erreur_identification', $exception->getMessage());

        $referenceInvitation = $request->getSession()->get(BrisPorteController::CLEF_SESSION_INVITATION_FRANCE_CONNECT);
        if (null !== $referenceInvitation) {
            $request->getSession()->remove(BrisPorteController::CLEF_SESSION_INVITATION_FRANCE_CONNECT);

            return new RedirectResponse($this->urlGenerator->generate('bris_porte_demarrer_depuis_invitation', ['reference' => $referenceInvitation]));
        }

        return new RedirectResponse($this->urlGenerator->generate($this->loginPageRoute));
    }
}
