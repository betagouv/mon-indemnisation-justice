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

    private static function has_fields(Request $request): bool
    {
      return (null !== $request->get('type',null));
    }

    private static function get_fields(Request $request): array
    {
      $type = $request->get('type',null);
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
      }
      return $fields;
    }

    #[Route(path: '/inscription', name: 'app_inscription', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function inscription(Request $request): Response
    {
        $user = $this->getUser();
        if(null !== $user)
          return $this->redirect('/redirect');

        $breadcrumb = $this->breadcrumb;
        $breadcrumb->add('homepage.title','app_homepage');
        $session = $request->getSession();
        /**
         * Le formulaire a bien été identifié
         *
         */
        if(true === self::has_fields($request)) {
          $fields = self::get_fields($request);
          if($fields['type'] === 'BRI')
            $breadcrumb->add('bris_porte.test_eligibilite.title','app_bris_porte_test_eligibilite');
          else {
            $session->set("test_eligibilite",null);
            return $this->redirect('/');
          }
          $breadcrumb->add('security.inscription.title',null);
          $session->set("test_eligibilite",$fields);
          dump($fields);
        }
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('security/inscription.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'breadcrumb' => $this->breadcrumb,
            'version' => $this->version
        ]);
    }
}
