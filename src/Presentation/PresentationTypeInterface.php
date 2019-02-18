<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

interface PresentationTypeInterface
{
    public function getSlug(): string;

    public function getRenderer(): PresentationRendererInterface;
}
