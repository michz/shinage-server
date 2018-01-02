<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 09.02.17
 * Time: 21:42
 */

namespace AppBundle\Service;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScheduledPresentation;
use AppBundle\Entity\Screen;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SchedulerService
{
    protected $em = null;
    protected $tokenStorage = null;

    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Screen $screen
     * @param bool   $fallbackToDefault
     * @return mixed
     */
    public function getCurrentPresentation(Screen $screen, $fallbackToDefault = true)
    {
        $em = $this->em;

        $query = $em->createQuery(
            'SELECT p
                    FROM AppBundle:ScheduledPresentation p
                    WHERE
                        (
                        (p.scheduled_start <= :now AND p.scheduled_end >= :now)
                        ) AND 
                        p.screen = :screen
                    ORDER BY p.scheduled_start ASC'
        )
            ->setParameter('now', date('Y-m-d H:i:s'))
            ->setParameter('screen', $screen);

        $results = $query->getResult();

        if (count($results) > 0) {
            /** @var ScheduledPresentation $p */
            $p = $results[0];
            return $p->getPresentation();
        }

        // no presentation scheduled, so return default
        if ($fallbackToDefault) {
            return $screen->getDefaultPresentation();
        }

        // don't fallback to default, so return null
        return null;
    }

    /**
     * Gets the current Presentation of the given Screen and writes it to the Entity.
     * @param Screen $screen
     */
    public function updateScreen(Screen $screen, $fallbackDefault = true)
    {
        $screen->setCurrentPresentation($this->getCurrentPresentation($screen, $fallbackDefault));
    }

    /**
     * Delete all scheduled entries of the given presentation.
     *
     * @param Presentation $presentation
     */
    public function deleteAllScheduledPresentationsForPresentation(Presentation $presentation)
    {
        $q = $this->em->createQuery(
            'delete from AppBundle:ScheduledPresentation p where p.presentation = :presentation'
        );
        $q->setParameter('presentation', $presentation);
        $numDeleted = $q->execute();
        $this->em->flush();
    }
}
