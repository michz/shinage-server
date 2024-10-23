<?php

declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\EventListener;

use App\Controller\Api\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException as HttpFoundationFileAccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SecurityCoreAccessDeniedException;

readonly class KernelExceptionListener
{
    public function __construct(
        private string $environment,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (0 !== \strpos($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $data = new \stdClass();
        $data->message = $event->getThrowable()->getMessage();
        $data->type = \get_class($event->getThrowable());
        $statusCode = $this->getStatusCode($event->getThrowable());

        if (\in_array($this->environment, ['dev', 'test'])) {
            $data->trace = $event->getThrowable()->getTraceAsString();
        }

        if (null === $event->getResponse()) {
            $event->setResponse(new Response());
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
