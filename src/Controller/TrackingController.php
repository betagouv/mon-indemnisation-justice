<?php
namespace App\Controller;

use App\Entity\Tracking;
use App\Entity\User;
use App\Service\TransparentPixelResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TrackingController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em
    )
    {

    }

    #[Route('/track.gif',name: 'app_tracking', methods: ['GET'])]
    public function trackEmailAction(Request $request): TransparentPixelResponse
    {
      $id = $request->query->get('id',null);
      $md5 = $request->query->get('md5',null);

      if (null!==$id && null!==$md5) {
        /** @var ?User $user */
        $user = $this->em->getRepository(User::class)->find($id);
        if(null!==$user && md5($user->getEmail())==$md5)
          $this->em->getRepository(Tracking::class)->add($user,Tracking::EVENT_OPEN_EMAIL_CREATE_ACCOUNT);
      }

      return new TransparentPixelResponse();
    }
}
