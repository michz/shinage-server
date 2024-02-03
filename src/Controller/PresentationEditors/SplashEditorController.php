<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\PresentationEditors;

use App\Entity\PresentationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SplashEditorController extends AbstractPresentationEditor
{
    public function editAction(Request $request, int $presentationId): Response
    {
        return new Response('Can not be edited.');
    }

    public function supports(PresentationInterface $presentation): bool
    {
        return 'splash' === $presentation->getType();
    }
}
