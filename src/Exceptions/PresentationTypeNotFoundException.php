<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Exceptions;

use Throwable;

class PresentationTypeNotFoundException extends \RuntimeException
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
