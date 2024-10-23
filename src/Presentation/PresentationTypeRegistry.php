<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use App\Exceptions\PresentationTypeNotFoundException;

class PresentationTypeRegistry implements PresentationTypeRegistryInterface
{
    /** @var PresentationTypeInterface[] */
    private array $types = [];

    public function addPresentationType(PresentationTypeInterface $type): void
    {
        $this->types[$type->getSlug()] = $type;
    }

    public function getPresentationType(string $slug): PresentationTypeInterface
    {
        if (isset($this->types[$slug])) {
            return $this->types[$slug];
        }

        throw new PresentationTypeNotFoundException($slug);
    }

    /**
     * {@inheritDoc}
     */
    public function getPresentationTypes(): array
    {
        return $this->types;
    }
}
