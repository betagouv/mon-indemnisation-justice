<?php

namespace App\Controller;

use App\Entity\Prejudice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/redirect', name: 'app_redirect')]
    public function redirection(): RedirectResponse
    {
        return $this->redirectToRoute('app_requerant_homepage');
    }

    #[Route('/', name: 'app_homepage', options: ['expose' => true])]
    #[Route('/declarer-un-prejudice', name: 'app_category', options: ['expose' => true])]
    public function category(): Response
    {
        return $this->render('homepage/category.html.twig', [
        ]);
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous', options: ['expose' => true])]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }

    #[Route('/suivi-de-mon-dossier', name: 'app_suivi_mon_dossier', options: ['expose' => true])]
    public function suiviDossier(Request $request): Response
    {
        /** @var string $raccourci */
        $raccourci = $request->get('raccourci') ?? '';

        $submittedToken = $request->getPayload()->get('_csrf_token');
        $statuts = [];
        if ($this->isCsrfTokenValid('authenticate', $submittedToken)) {
            $prejudice = $this
              ->em
              ->getRepository(Prejudice::class)
              ->findOneBy(['raccourci' => $raccourci])
            ;
            if (null !== $prejudice) {
                $tmp = $prejudice->getStatuts();
                foreach ($tmp as $statut) {
                    $statuts[] = [$statut->getDate()->format('d/m/Y H:i'), $statut->getLibelle()];
                }
            }
        }

        return $this->render('homepage/suivi_dossier.html.twig', [
            'raccourci' => $raccourci,
            'statuts' => $statuts,
        ]);
    }

    #[Route('/conditions-generales-d-utilisation', name: 'app_cgu', options: ['expose' => true])]
    public function cgu(): Response
    {
        return $this->render('homepage/cgu.html.twig');
    }
}
