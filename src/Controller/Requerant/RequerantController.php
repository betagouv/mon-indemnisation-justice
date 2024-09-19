<?php

namespace App\Controller\Requerant;

use App\Entity\Requerant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class RequerantController extends AbstractController
{
    protected function getRequerant(): ?Requerant
    {
        $user = $this->getUser();
        return $user instanceof Requerant ? $user : null;
    }
}