<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Mirror;

use JMS\Serializer\Annotation as JMS;

class Settings
{
    #[JMS\Type('string')]
    protected string $url = '';

    #[JMS\Type('string')]
    protected string $type = 'jsonp';

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
