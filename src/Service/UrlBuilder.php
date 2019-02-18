<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class UrlBuilder implements UrlBuilderInterface
{
    /** @var RouterInterface */
    private $router;

    public function __construct(
        RouterInterface $router
    ) {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsoluteRouteBasedOnRequest(Request $request, string $route, array $parameters = []): string
    {
        return $request->getScheme() . '://' . $request->getHttpHost() . $this->router->generate($route, $parameters);
    }
}
