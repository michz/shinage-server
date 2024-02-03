<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

class GenericPresentationType implements PresentationTypeInterface
{
    private string $slug;

    private PresentationRendererInterface $presentationRenderer;

    public function __construct(
        string $slug,
        PresentationRendererInterface $presentationRenderer
    ) {
        $this->slug = $slug;
        $this->presentationRenderer = $presentationRenderer;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getRenderer(): PresentationRendererInterface
    {
        return $this->presentationRenderer;
    }
}
