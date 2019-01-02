<?php

namespace spec\App\Service;

use App\Service\PathConcatenator;
use App\Service\PathConcatenatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathConcatenatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PathConcatenator::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(PathConcatenatorInterface::class);
    }

    public function it_can_concat_two_simple()
    {
        $this
            ->concatTwo('left', 'right')
            ->shouldReturn('left/right');
    }

    public function it_can_concat_two_trailing_slash()
    {
        $this
            ->concatTwo('left/', 'right/')
            ->shouldReturn('left/right/');
    }

    public function it_can_concat_two_leading_slash()
    {
        $this
            ->concatTwo('/left', '/right')
            ->shouldReturn('/left/right');
    }

    public function it_can_concat_two_double_slash()
    {
        $this
            ->concatTwo('/left/', '/right/')
            ->shouldReturn('/left/right/');
    }
}
