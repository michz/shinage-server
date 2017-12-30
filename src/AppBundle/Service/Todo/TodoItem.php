<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  29.01.17
 * @time     :  11:01
 */
namespace AppBundle\Service\Todo;

class TodoItem
{
    protected $type = 'TODO';
    protected $text = '';
    protected $file = '';
    protected $severity = 0;
    protected $line = 0;

    public function __construct($text, $file = '', $line = 0, $type = 'TODO', $severity = 0)
    {
        $this->type = $type;
        $this->text = $text;
        $this->file = $file;
        $this->line = $line;
        $this->severity = $severity;
    }


    public function getType()
    {
        return $this->type;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getSeverity()
    {
        return $this->severity;
    }
}
