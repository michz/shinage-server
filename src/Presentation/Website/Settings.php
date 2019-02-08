<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Website;

use JMS\Serializer\Annotation as JMS;

class Settings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $url = '';

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
