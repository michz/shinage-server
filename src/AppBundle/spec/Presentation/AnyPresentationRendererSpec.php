<?php

namespace spec\AppBundle\Presentation;

use AppBundle\Entity\Presentation;
use AppBundle\Presentation\AnyPresentationRenderer;
use AppBundle\Presentation\AnyPresentationRendererInterface;
use AppBundle\Presentation\PresentationRendererInterface;
use AppBundle\Presentation\PresentationTypeInterface;
use AppBundle\Presentation\PresentationTypeRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AnyPresentationRendererSpec extends ObjectBehavior
{
    public function let(
        PresentationTypeRegistryInterface $presentationTypeRegistry
    ) {
        $this->beConstructedWith($presentationTypeRegistry);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AnyPresentationRenderer::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(AnyPresentationRendererInterface::class);
    }

    public function it_can_render(
        PresentationTypeRegistryInterface $presentationTypeRegistry,
        PresentationTypeInterface $presentationType,
        PresentationRendererInterface $presentationRenderer,
        Presentation $presentation
    ) {
        $typeSlug = 'test_type';
        $renderedPresentation = 'RENDERED PRESENTATION DATA';

        $presentation->getType()
            ->willReturn($typeSlug);

        $presentationTypeRegistry->getPresentationType(Argument::is($typeSlug))
            ->shouldBeCalled()
            ->willReturn($presentationType);
        $presentationType->getRenderer()
            ->willReturn($presentationRenderer);
        $presentationType->getSlug()
            ->willReturn($typeSlug);

        $presentationRenderer->render(Argument::is($presentation->getWrappedObject()))
            ->shouldBeCalled()
            ->willReturn($renderedPresentation);

        $this->render($presentation)
            ->shouldReturn($renderedPresentation);
    }
}
