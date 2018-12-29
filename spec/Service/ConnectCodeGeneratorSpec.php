<?php

namespace spec\App\Service;

use App\Service\ConnectCodeGenerator;
use App\Service\ConnectCodeGeneratorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectCodeGeneratorSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager,
        ObjectRepository $repository
    ) {
        $this->beConstructedWith(
            $entityManager,
            15
        );

        $entityManager
            ->getRepository(Argument::any())
            ->willReturn($repository);
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
        ObjectRepository $repository
    ) {
        $repository
            ->findBy(Argument::withKey('connect_code'))
            ->shouldBeCalledTimes(2)
            ->willReturn([new \stdClass()], []);

        $return = $this->generateUniqueConnectcode()->getWrappedObject();

        if (\strlen($return) !== 15) {
            throw new \Exception('String length does not match.');
        }
    }
}
