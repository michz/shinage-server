<?php

namespace spec\App\Presentation;

use App\Presentation\GenericSettingsReader;
use App\Presentation\PresentationSettingsInterface;
use App\Presentation\SettingsReaderInterface;
use JMS\Serializer\SerializerInterface;
use PhpSpec\ObjectBehavior;

class GenericSettingsReaderSpec extends ObjectBehavior
{
    const TYPE = 'ExampleType';

    public function let(
        SerializerInterface $serializer
    ) {
        $this->beConstructedWith($serializer, self::TYPE);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GenericSettingsReader::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(SettingsReaderInterface::class);
    }

    public function i_can_read_settings(
        SerializerInterface $serializer,
        PresentationSettingsInterface $presentationSettings
    ) {
        $inputData = 'this is just sample input';
        $serializer->deserialize($inputData, self:: TYPE, 'json')
            ->shouldBeCalled()
            ->willReturn($presentationSettings);

        $this->get($inputData)
            ->shouldReturn($presentationSettings);
    }
}
