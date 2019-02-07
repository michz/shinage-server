<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\EventListener;

use App\Controller\Api\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException as HttpFoundationFileAccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SecurityCoreAccessDeniedException;

class KernelExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if (0 !== strpos($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $data = new \stdClass();
        $data->message = $event->getException()->getMessage();
        $data->type = \get_class($event->getException());
        $statusCode = $this->getStatusCode($event->getException());

        if (null === $event->getResponse()) {
            $event->setResponse(Response::create());
        }

        $event->getResponse()
            ->setStatusCode($statusCode)
            ->setContent(\json_encode($data));
    }

    private function getStatusCode(\Throwable $throwable): int
    {
        switch (\get_class($throwable)) {
            case AccessDeniedException::class:
            case SecurityCoreAccessDeniedException::class:
            case HttpFoundationFileAccessDeniedException::class:
                return Response::HTTP_FORBIDDEN;
            case NotFoundHttpException::class:
                return Response::HTTP_NOT_FOUND;
            case BadRequestHttpException::class:
                return Response::HTTP_BAD_REQUEST;
        }
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
