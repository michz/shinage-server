<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:12
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\User;
use AppBundle\Service\QuotaCalculator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UsersController extends Controller
{
    /**
     * @Route("/adm/users", name="admin-users")
     */
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var EntityRepository $repUsers */
        $repUsers = $em->getRepository('AppBundle:User');

        $users = $repUsers->findAll();
        $quotas = [];

        /** @var QuotaCalculator $quotaCalculator */
        $quotaCalculator = $this->get('app.quotacalculator');

        /** @var User $user */
        foreach ($users as $user) {
            $quotas[$user->getId()] = $quotaCalculator->getQuotaForUser($user);
        }

        return $this->render('adm/users.html.twig', [
            'users'  => $users,
            'quotas' => $quotas,
        ]);
    }
}
