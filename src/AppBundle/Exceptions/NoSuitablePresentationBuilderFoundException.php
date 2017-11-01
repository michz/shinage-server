<?php

namespace AppBundle\Exceptions;

use Throwable;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  27.10.17
 * @time     :  12:15
 */
class NoSuitablePresentationBuilderFoundException extends \Exception
{
    private $type;

    public function __construct($type, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }
}
