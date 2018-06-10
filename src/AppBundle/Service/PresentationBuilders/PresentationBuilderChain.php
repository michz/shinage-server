<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Exceptions\NoSuitablePresentationBuilderFoundException;

class PresentationBuilderChain
{
    /** @var PresentationBuilderInterface[]|array */
    private $builders = [];

    /** @var string[]|array */
    private $types = [];

    public function addBuilder(PresentationBuilderInterface $builder): void
    {
        $this->builders[] = $builder;
        $this->types = array_merge($this->types, $builder->getSupportedTypes());
    }

    public function getBuilderForPresentation(Presentation $presentation): PresentationBuilderInterface
    {
        /** @var PresentationBuilderInterface $builder */
        foreach ($this->builders as $builder) {
            if ($builder->supports($presentation)) {
                return $builder;
            }
        }

        throw new NoSuitablePresentationBuilderFoundException($presentation->getType());
    }

    /**
     * @return string[]|array
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
