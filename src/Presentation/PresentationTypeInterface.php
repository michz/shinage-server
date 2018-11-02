<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

interface PresentationTypeInterface
{
    public function getSlug(): string;

    public function getRenderer(): PresentationRendererInterface;
}
