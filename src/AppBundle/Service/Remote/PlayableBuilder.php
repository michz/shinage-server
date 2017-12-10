<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 25.06.2017
 * Time: 11:01
 */

namespace AppBundle\Service\Remote;

use AppBundle\Entity\Presentation;
use AppBundle\Entity\ScreenRemote\PlayablePresentation;
use AppBundle\Entity\Slide;
use Symfony\Component\Routing\Router;

class PlayableBuilder
{
    /** @var Router */
    protected $router;

    /**
     * PlayableBuilder constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function build(Presentation $presentation, $hostScheme)
    {
        $playable = new PlayablePresentation();
        $playable->lastModified = $presentation->getLastModified();

        return $playable;
    }

    public function getPlayerSlideType($internalType)
    {
        return ucwords($internalType);
    }
}
