<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  17:47
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PoolController extends Controller
{

    /**
     * @Route("/pool/{path}", name="pool-get", requirements={"path": ".*"})
     */
    public function getAction(string $path)
    {
        var_dump($path);
        exit;
    }
}
