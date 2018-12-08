<?php

namespace spec\App\Presentation;

use App\Entity\Presentation;
use App\Entity\PresentationInterface;
use App\Presentation\AnyPresentationRenderer;
use App\Presentation\AnyPresentationRendererInterface;
use App\Presentation\PresentationRendererInterface;
use App\Presentation\PresentationTypeInterface;
use App\Presentation\PresentationTypeRegistryInterface;
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
        PresentationInterface $presentation
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
