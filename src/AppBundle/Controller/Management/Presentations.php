<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 05.01.17
 * Time: 09:21
 */

namespace AppBundle\Controller\Management;

use AppBundle\Entity\Presentation;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exceptions\NoScreenGivenException;
use AppBundle\Entity\Screen;
use AppBundle\Service\FilePool;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;
use AppBundle\Entity\User;
use AppBundle\Entity\Slide;
use AppBundle\Exceptions\SlideTypeNotImplemented;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Presentations extends Controller
{

    /**
     * @Route("/manage/presentations", name="management-presentations")
     */
    public function managePresentationsAction(Request $request)
    {
        // @TODO Security
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $pres = $this->getPresentationsForUser($user);

        return $this->render('manage/presentations/pres-main.html.twig', [
            'presentations' => $pres,
        ]);
    }

    public function getPresentationsForUser(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        return $user->getPresentations($em);
    }
}
