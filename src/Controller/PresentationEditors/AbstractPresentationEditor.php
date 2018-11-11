<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\PresentationEditors;

use App\Entity\PresentationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractPresentationEditor extends Controller
{
    abstract public function supports(PresentationInterface $presentation): bool;

    public function getPresentation(int $id): PresentationInterface
    {
        // @TODO catch error (not found) and show meaningful error message
        return $this->getDoctrine()->getEntityManager()->find('App:Presentation', $id);
    }
}
