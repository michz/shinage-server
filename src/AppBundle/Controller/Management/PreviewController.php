<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 26.12.17
 * Time: 18:04
 */

namespace AppBundle\Controller\Management;

use AppBundle\Service\ScreenAssociation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PreviewController extends Controller
{

    /**
     * @Route("/manage/preview", name="management-preview")
     */
    public function previewAction()
    {
        /** @var User $user   user that is logged in*/
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // screens that are associated to the user or to its organizations
        // (should be last call, so that newly created screens are recognized)
        $assoc = $this->get('app.screenassociation'); /** @var ScreenAssociation $assoc */
        $screens = $assoc->getScreensForUser($user);

        dump($screens);

        return $this->render('manage/preview.html.twig', [
            'screens' => $screens,
        ]);
    }
}
