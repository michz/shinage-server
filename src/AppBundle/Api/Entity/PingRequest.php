<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.02.17
 * @time     :  17:31
 */

namespace AppBundle\Api\Entity;

class PingRequest
{

    protected $data;


    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
