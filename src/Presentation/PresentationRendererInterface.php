<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use App\Entity\PresentationInterface;

interface PresentationRendererInterface
{
    public function render(PresentationInterface $presentation): string;

    public function getLastModified(PresentationInterface $presentation): \DateTime;
}
