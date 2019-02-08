<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\Transform;

use App\Entity\Presentation;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;

class PresentationTransformerContext implements Context
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Transform :presentation
     * @Transform /^presentation "([^"]+)"$/
     * @Transform /^The presentation "([^"]+)"$/
     * @Transform /^the presentation "([^"]+)"$/
     */
    public function getPresentation(string $title): Presentation
    {
        $repo = $this->entityManager->getRepository(Presentation::class);
        return $repo->findOneBy(['title' => $title]);
    }
}
