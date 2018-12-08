<?php

namespace spec\App\Presentation;

use App\Presentation\PresentationTypeInterface;
use App\Presentation\PresentationTypeRegistry;
use App\Presentation\PresentationTypeRegistryInterface;
use PhpSpec\ObjectBehavior;

class PresentationTypeRegistrySpec extends ObjectBehavior
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(PresentationTypeRegistry::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(PresentationTypeRegistryInterface::class);
    }

    public function it_can_add_presentation_type(
        PresentationTypeInterface $presentationType
    ) {
        $presentationType->getSlug()
            ->willReturn('type');

        $this->addPresentationType($presentationType);
    }

    public function it_can_get_presentation_type(
        PresentationTypeInterface $presentationType1,
        PresentationTypeInterface $presentationType2
    ) {
        $presentationType1->getSlug()
            ->willReturn('type1');
        $presentationType2->getSlug()
            ->willReturn('type2');

        $this->addPresentationType($presentationType1);
        $this->addPresentationType($presentationType2);

        $this->getPresentationType('type2')
            ->shouldReturn($presentationType2);
        $this->getPresentationType('type1')
            ->shouldReturn($presentationType1);
    }

    public function it_can_get_presentation_types(
        PresentationTypeInterface $presentationType1,
        PresentationTypeInterface $presentationType2
    ) {
        $presentationType1->getSlug()
            ->willReturn('type1');
        $presentationType2->getSlug()
            ->willReturn('type2');

        $this->addPresentationType($presentationType1);
        $this->addPresentationType($presentationType2);

        $this->getPresentationTypes()
            ->shouldReturn([
                'type1' => $presentationType1,
                'type2' => $presentationType2
            ]);
    }
}
