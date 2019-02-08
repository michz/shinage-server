<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

interface UrlBuilderInterface
{
    /**
     * @param array|string[]|mixed[] $parameters
     */
    public function getAbsoluteRouteBasedOnRequest(Request $request, string $route, array $parameters = []): string;
}
