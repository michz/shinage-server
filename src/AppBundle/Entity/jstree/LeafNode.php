<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  16:23
 */

namespace AppBundle\Entity\jstree;

class FileNode
{
    /**
     * @var string
     */
    public $text;

    /**
     * @var FileNode[]
     */
    public $children;

    /**
     * @var string
     */
    public $icon;

    /**
     * @var FileNodeState
     */
    public $state;

    /**
     * FileNode constructor.
     */
    public function __construct()
    {
        $this->state = new FileNodeState();
    }
}
