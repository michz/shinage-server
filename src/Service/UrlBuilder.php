<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

readonly class UrlBuilder implements UrlBuilderInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsoluteRouteBasedOnRequest(Request $request, string $route, array $parameters = []): string
    {
        return $request->getScheme() . '://' . $request->getHttpHost() . $this->router->generate($route, $parameters);
    }
}
