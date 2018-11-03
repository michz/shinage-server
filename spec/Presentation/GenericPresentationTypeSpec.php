<?php

namespace spec\App\Presentation;

use App\Presentation\GenericPresentationType;
use App\Presentation\PresentationRendererInterface;
use App\Presentation\PresentationTypeInterface;
use PhpSpec\ObjectBehavior;

class GenericPresentationTypeSpec extends ObjectBehavior
{
    const TYPE = 'ExampleType';

    public function let(
        PresentationRendererInterface $presentationRenderer
    ) {
        $this->beConstructedWith(self::TYPE, $presentationRenderer);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GenericPresentationType::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(PresentationTypeInterface::class);
    }

    public function it_can_get_slug() {
        $this->getSlug()
            ->shouldReturn(self::TYPE);
    }

    public function it_can_get_renderer(
        PresentationRendererInterface $presentationRenderer
    ) {
        $this->getRenderer()
            ->shouldReturn($presentationRenderer);
    }
}
