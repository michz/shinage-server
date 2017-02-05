<?php

namespace AppBundle\Api;

use AppBundle\Api\Entity\PingRequest;
use AppBundle\Service\FilePool;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\TodoList;
use Symfony\Component\Intl\Data\Bundle\Reader\JsonBundleReader;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("Ping", pluralize=false)
 */
class Ping extends FOSRestController
{

    public function postPingAction(PingRequest $pingRequest)
    {
        var_dump($pingRequest);

        $data = ['ping' => 'pong'];
        $view = $this->view($data, 200)
            #->setTemplate("MyBundle:Users:getUsers.html.twig")
            #->setTemplateVar('users')
        ;

        return $this->handleView($view);
    }
}
