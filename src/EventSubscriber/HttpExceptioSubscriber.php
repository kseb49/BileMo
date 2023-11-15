<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TypeError;

class HttpExceptioSubscriber implements EventSubscriberInterface
{


    /**
     * Grab the Exception event then the HttpException to return a Json response
     *
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof HttpException) {
            $responseBody
            =
            [
                "status" => $exception->getStatusCode(),
                "message" => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($responseBody));
            return;
        }

        if ($exception instanceof TypeError) {
            $responseBody = ["message" => $exception->getMessage()];
            $event->setResponse(new JsonResponse($responseBody, 400));
            return;
        }

        $responseBody = ["message" => $exception->getMessage()];
        $event->setResponse(new JsonResponse($responseBody, 500));
        return;

    }


    /**
     * The event to listen ant the method to call
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];

    }


}
