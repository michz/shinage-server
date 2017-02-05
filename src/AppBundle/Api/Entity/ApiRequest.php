<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.02.17
 * @time     :  17:31
 */

namespace AppBundle\Api\Entity;

class ApiRequest
{

    protected $auth;

    protected $data;


    public function getAuth()
    {
        return $this->auth;
    }

    public function setAuth($auth)
    {
        $this->auth = $auth;
        return $this;
    }

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
