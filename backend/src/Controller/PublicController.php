<?php

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class PublicController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        #[Autowire(service: 'security.password_hasher')]
        protected readonly UserPasswordHasherInterface $hasher,
        protected readonly TokenStorageInterface $tokenStorage,
    ) {

    }

    #[Route('/dysfonctionnement/{extra}', name: 'public_dysfonctionnement_react', requirements: ['extra' => '.*'], env: ['dev', 'test', 'ci', 'develop'])]
    #[Route('/delai-deraisonnable/{extra}', name: 'public_delai_resaisonnable_react', requirements: ['extra' => '.*'], env: ['dev', 'test', 'ci', 'develop'])]
    public function react(): Response
    {
        return $this->render('public/public.html.twig', [
            'react' => [],
        ]);
    }

    #[Route('/dysfonctionnement/avocats/inscription', name: 'public_dysfonctionnement_avocats_inscription', methods: ['POST'], env: ['dev', 'test', 'ci', 'develop'])]
    public function inscriptionAvocat(Request $request): Response
    {
        return $this->render('public/public.html.twig', [
            'react' => [
                'connexion' => [
                    'username' => $request->request->get('username'),
                    'password' => $request->request->get('password'),
                ],
                'erreurs' => [
                    'usager' => 'Compte avocat non reconnu',
                ],
            ],
        ]);
    }

    #[Route('/dysfonctionnement/avocats/connexion', name: 'public_dysfonctionnement_avocats_connexion', methods: ['POST'], env: ['dev', 'test', 'ci', 'develop'])]
    public function connexionAvocat(Request $request): Response
    {
        $usager = $this->em->getRepository(Usager::class)->findAvocat($request->request->get('username'));

        if ($this->hasher->verify($request->request->get('username'), $usager->getPassword())) {
            $token = new UsernamePasswordToken(
                $usager,
                'requerant',
                $usager->getRoles()
            );
            $this->tokenStorage->setToken($token);

            return $this->redirectToRoute('requerant_home_index');
        }

        return $this->render('public/public.html.twig', [
            'react' => [
                'connexion' => [
                    'username' => $request->request->get('username'),
                    'password' => $request->request->get('password'),
                ],
                'erreurs' => [
                    'usager' => 'Compte avocat non reconnu',
                ],
            ],
        ]);
    }
}
