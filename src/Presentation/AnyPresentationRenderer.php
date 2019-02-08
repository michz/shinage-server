<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use App\Entity\PresentationInterface;

class AnyPresentationRenderer implements AnyPresentationRendererInterface
{
    /** @var PresentationTypeRegistryInterface */
    private $presentationTypeRegistry;

    public function __construct(
        PresentationTypeRegistryInterface $presentationTypeRegistry
    ) {
        $this->presentationTypeRegistry = $presentationTypeRegistry;
    }

    public function render(PresentationInterface $presentation): string
    {
        $type = $this->presentationTypeRegistry->getPresentationType($presentation->getType());
        $renderer = $type->getRenderer();
        return $renderer->render($presentation);
    }
}
