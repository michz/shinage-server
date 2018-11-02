<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use App\Entity\Presentation;

class AnyPresentationRenderer implements AnyPresentationRendererInterface
{
    /** @var PresentationTypeRegistryInterface */
    private $presentationTypeRegistry;

    public function __construct(
        PresentationTypeRegistryInterface $presentationTypeRegistry
    ) {
        $this->presentationTypeRegistry = $presentationTypeRegistry;
    }

    public function render(Presentation $presentation): string
    {
        $type = $this->presentationTypeRegistry->getPresentationType($presentation->getType());
        $renderer = $type->getRenderer();
        return $renderer->render($presentation);
    }
}
