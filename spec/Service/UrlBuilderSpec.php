<?php

namespace spec\App\Service;

use App\Service\UrlBuilder;
use App\Service\UrlBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class UrlBuilderSpec extends ObjectBehavior
{
    public function let(
        RouterInterface $router
    ) {
        $this->beConstructedWith($router);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UrlBuilder::class);
    }

    public function it_implements_interface()
    {
        $this->shouldImplement(UrlBuilderInterface::class);
    }

    public function it_can_get_absolute_route_based_on_request(
        RouterInterface $router,
        Request $request
    ) {
        $expected = 'https://testhost:12345/route/test';
        $route = 'route';
        $parameters = [];

        $request
            ->getScheme()
            ->willReturn('https');
        $request
            ->getHttpHost()
            ->willReturn('testhost:12345');

        $router
            ->generate($route, $parameters)
            ->willReturn('/route/test');

        $this
            ->getAbsoluteRouteBasedOnRequest($request, $route, $parameters)
            ->shouldReturn($expected);
    }
}
