<?php

namespace spec\App\Service;

use App\Service\ConfirmationTokenGenerator;
use App\Service\ConfirmationTokenGeneratorInterface;
use PhpSpec\ObjectBehavior;

class ConfirmationTokenGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConfirmationTokenGenerator::class);
    }

    public function it_implements_interface(): void
    {
        $this->shouldImplement(ConfirmationTokenGeneratorInterface::class);
    }

    public function it_can_generate_token(): void
    {
        $this->generateConfirmationToken()
            ->shouldBeString();
    }
}
