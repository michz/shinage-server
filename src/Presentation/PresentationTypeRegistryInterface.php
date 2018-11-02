<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

interface PresentationTypeRegistryInterface
{
    public function addPresentationType(PresentationTypeInterface $type): void;

    public function getPresentationType(string $slug): PresentationTypeInterface;

    /**
     * @return string[]|array
     */
    public function getPresentationTypes(): array;
}
