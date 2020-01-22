<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Firewall;

use App\Security\Authentication\Token\ApiScreenToken;
use App\Security\VolatileScreenUser;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ApiScreenListener implements ListenerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthenticationManagerInterface */
    private $authenticationManager;

    /** @var EntityRepository */
    private $screenRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        EntityRepository $screenRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->screenRepository = $screenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->headers->has('x-screen-guid')) {
            return;
        }

        $token = new ApiScreenToken();

        $screen = $this->screenRepository->findBy(['guid' => $request->headers->get('x-screen-guid')]);
        if (empty($screen)) {
            throw new AuthenticationException('Screen GUID invalid.');
        }

        $token->setUser(new VolatileScreenUser($screen[0]));

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            // ... you might log something here

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
            // $token = $this->tokenStorage->getToken();
            // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
            //     $this->tokenStorage->setToken(null);
            // }
            // return;
        }

        // @TODO Do not deny but be 'unauthenticated'
        // By default deny authorization
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}
