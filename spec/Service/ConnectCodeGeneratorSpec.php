<?php

namespace spec\App\Service;

use App\Entity\Screen;
use App\Repository\ScreenRepositoryInterface;
use App\Service\ConnectCodeGenerator;
use App\Service\ConnectCodeGeneratorInterface;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectCodeGeneratorSpec extends ObjectBehavior
{
    public function let(
        ScreenRepositoryInterface $screenRepository,
    ) {
        $this->beConstructedWith(
            $screenRepository,
            15
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConnectCodeGenerator::class);
    }

    public function it_implements_correct_interface()
    {
        $this->shouldImplement(ConnectCodeGeneratorInterface::class);
    }

    public function it_can_generate_unique_connectcode(
        ScreenRepositoryInterface $screenRepository,
        Screen $screen,
    ) {
        $screenRepository
            ->getScreenByConnectCode(Argument::any())
            ->shouldBeCalledTimes(2)
            ->willReturn($screen, null);

        $return = $this->generateUniqueConnectcode()->getWrappedObject();

        if (\strlen($return) !== 15) {
            throw new \Exception('String length does not match.');
        }
    }
}
