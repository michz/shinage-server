<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Exceptions;

use Throwable;

class NoSuitablePresentationBuilderFoundException extends \RuntimeException
{
    /** @var string */
    private $type;

    public function __construct(string $type, string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
