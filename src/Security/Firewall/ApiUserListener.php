<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Security\Firewall;

use App\Entity\Api\AccessKey;
use App\Security\Authentication\Token\ApiUserToken;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ApiUserListener implements ListenerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthenticationManagerInterface */
    private $authenticationManager;

    /** @var EntityRepository */
    private $apiTokenRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        EntityRepository $apiTokenRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->apiTokenRepository = $apiTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->headers->has('x-api-token')) {
            return;
        }

        $token = new ApiUserToken();
        /** @var AccessKey|null $apiToken */
        $apiToken = $this->apiTokenRepository->findBy(['code' => $request->headers->get('x-api-token')]);
        if (empty($apiToken)) {
            throw new AuthenticationException('API token invalid.');
        }

        $user = $apiToken[0]->getOwner();
        if (null === $user) {
            throw new AuthenticationException('API token invalid.');
        }

        $token->setUser($user);

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
