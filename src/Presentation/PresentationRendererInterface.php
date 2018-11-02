<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use App\Entity\Presentation;

interface PresentationRendererInterface
{
    public function render(Presentation $presentation): string;

    public function getLastModified(Presentation $presentation): \DateTime;
}
