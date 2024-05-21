<?php

namespace App\Controller;

use App\Service\Version\Version;
use App\Service\Breadcrumb\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb,
      private AuthenticationUtils $authenticationUtils,
      private Version $version
    ) {

    }

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function login(): Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $user = $this->getUser();
        if(null !== $user)
          return $this->redirect('/redirect');

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/inscription', name: 'app_inscription', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function inscription(Request $request): Response
    {
        $breadcrumb = $this->breadcrumb;
        $breadcrumb->add('homepage.title','app_homepage');

        $session = $request->getSession();
        $type = $request->get('type') ?? 'NONE';
        $fields=['type' => $type];
        switch($type) {
          case 'BRI':
            $fields=array_merge($fields,[
              'dateOperationPJ' => null,
              'numeroPV' => null,
              'numeroParquet' => null,
              'isErreurPorte' => null
            ]);
            foreach($fields as $field => $value)
              $fields[$field]=$request->get($field);
            break;
          default:
            $session->set("test_eligibilite",null);
            return $this->redirect('/');
        }
        $session->set("test_eligibilite",$fields);
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $user = $this->getUser();
        if(null !== $user)
          return $this->redirect('/redirect');

        return $this->render('security/inscription.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'breadcrumb' => $this->breadcrumb,
            'version' => $this->version
        ]);
    }
}
