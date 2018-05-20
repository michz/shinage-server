<?php
declare(strict_types=1);

namespace mztx\ShinageOnlinePlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function indexAction()
    {
        return new Response('TODO: Index with explanation.');
    }
}
