<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Presentation;

class GenericPresentationType implements PresentationTypeInterface
{
    /** @var string */
    private $slug;

    /** @var PresentationRendererInterface */
    private $presentationRenderer;

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
