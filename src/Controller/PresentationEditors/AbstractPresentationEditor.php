<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\PresentationEditors;

use App\Entity\Presentation;
use App\Entity\PresentationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractPresentationEditor extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    abstract public function supports(PresentationInterface $presentation): bool;

    public function getPresentation(int $id): PresentationInterface
    {
        // @TODO catch error (not found) and show meaningful error message
        return $this->entityManager->find(Presentation::class, $id);
    }
}
