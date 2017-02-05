<?php
/**
 * Created by solutionDrive GmbH.
 *
 * @author   :  Michael Zapf <mz@solutionDrive.de>
 * @date     :  05.02.17
 * @time     :  17:57
 * @copyright:  2016 solutionDrive GmbH
 */

namespace AppBundle\Api\Entity;

class Auth
{

    protected $key;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return self::class
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
}
