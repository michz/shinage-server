<?php

namespace AppBundle\Entity\PresentationSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  05.11.17
 * @time     :  16:47
 */
class Mirror
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $url;

    /**
     * @var string
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Mirror
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Mirror
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
