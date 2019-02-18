<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\EventListener;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationListener implements EventSubscriberInterface
{
    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return string[]|array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInitialize',
        ];
    }

    public function onRegistrationInitialize(GetResponseUserEvent $event): void
    {
        $url = $this->router->generate('registration');
        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }
}
