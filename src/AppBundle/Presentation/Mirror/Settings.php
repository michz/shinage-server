<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Presentation\Mirror;

use JMS\Serializer\Annotation as JMS;

class Settings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $url;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type;

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
