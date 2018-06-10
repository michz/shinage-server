<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 *
 * @date     :  19.11.17
 *
 * @time     :  10:42
 */
abstract class AbstractPresentationEditor extends Controller
{
    abstract public function supports(Presentation $presentation): bool;

    public function getPresentation(int $id): Presentation
    {
        // @TODO catch error (not found) and show meaningful error message
        return $this->getDoctrine()->getEntityManager()->find('AppBundle:Presentation', $id);
    }
}
