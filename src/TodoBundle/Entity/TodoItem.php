<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace mztx\TodoBundle\Entity;

class TodoItem
{
    /** @var string */
    protected $type = 'TODO';

    /** @var string */
    protected $text = '';

    /** @var string */
    protected $file = '';

    /** @var int */
    protected $severity = 0;

    /** @var int */
    protected $line = 0;

    public function __construct(
        string $text,
        string $file = '',
        int $line = 0,
        string $type = 'TODO',
        int $severity = 0
    ) {
        $this->type = $type;
        $this->text = $text;
        $this->file = $file;
        $this->line = $line;
        $this->severity = $severity;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getSeverity(): int
    {
        return $this->severity;
    }
}
