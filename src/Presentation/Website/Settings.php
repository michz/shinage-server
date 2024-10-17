<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Website;

use JMS\Serializer\Annotation as JMS;

class Settings
{
    #[JMS\Type('string')]
    private string $url = '';

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
