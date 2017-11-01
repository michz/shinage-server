<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;
use AppBundle\Exceptions\NoSuitablePresentationBuilderFoundException;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  27.10.17
 * @time     :  12:04
 */
class PresentationBuilderChain
{
    /** @var array */
    private $builders = [];

    public function addBuilder(PresentationBuilderInterface $builder)
    {
        $this->builders[] = $builder;
    }

    /**
     * @param Presentation $presentation
     *
     * @return PresentationBuilderInterface
     * @throws NoSuitablePresentationBuilderFoundException
     */
    public function getBuilderForPresentation(Presentation $presentation)
    {
        /** @var PresentationBuilderInterface $builder */
        foreach ($this->builders as $builder) {
            if ($builder->supports($presentation)) {
                return $builder;
            }
        }

        throw new NoSuitablePresentationBuilderFoundException($presentation->getType());
    }
}
