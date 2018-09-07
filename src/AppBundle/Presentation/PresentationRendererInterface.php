<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Presentation;

use AppBundle\Entity\Presentation;

interface PresentationRendererInterface
{
    public function render(Presentation $presentation): string;
}
