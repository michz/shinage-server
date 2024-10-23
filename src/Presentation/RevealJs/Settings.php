<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\RevealJs;

use JMS\Serializer\Annotation as JMS;

class Settings
{
    #[JMS\Type('string')]
    protected string $content = '';

    #[JMS\Type('string')]
    protected string $revealSettings = '';

    #[JMS\Type('integer')]
    protected int $width = 1280;

    #[JMS\Type('integer')]
    protected int $height = 720;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getRevealSettings(): string
    {
        return $this->revealSettings;
    }

    public function setRevealSettings(string $revealSettings): self
    {
        $this->revealSettings = $revealSettings;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }
}
