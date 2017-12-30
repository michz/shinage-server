<?php

namespace AppBundle\Controller\PresentationEditors;

use AppBundle\Entity\Presentation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  19.11.17
 * @time     :  10:42
 */
abstract class AbstractPresentationEditor extends Controller
{
    /**
     * @param Presentation $presentation
     *
     * @return bool
     */
    abstract public function supports(Presentation $presentation): bool;

    /**
     * @param int $id
     *
     * @return Presentation
     */
    public function getPresentation(int $id): Presentation
    {
        // @TODO catch error (not found) and show meaningful error message
        return $this->getDoctrine()->getEntityManager()->find('AppBundle:Presentation', $id);
    }
}
