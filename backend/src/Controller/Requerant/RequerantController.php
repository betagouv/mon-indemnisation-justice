<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class RequerantController extends AbstractController
{
    protected function getRequerant(): ?Usager
    {
        $user = $this->getUser();

        return $user instanceof Usager ? $user : null;
    }
}
