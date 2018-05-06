<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 11.02.18
 * Time: 12:51
 */

namespace AppBundle\Controller\Management\Screens;

use AppBundle\Entity\Screen;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ScreenRightsController extends Controller
{
    /**
     * @Route("/manage/screen/{guid}/rights", name="management-screen-rights", requirements={"guid": "[^/]*"})
     */
    public function indexAction(/** @scrutinizer ignore-unused */ Request $request, string $guid)
    {
        /** @var User $user   user that is logged in*/
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $screen = $em->find(Screen::class, $guid);
        
        return $this->render('manage/screens/offline.html.twig', [
            'screen' => $screen
        ]);
    }
}
