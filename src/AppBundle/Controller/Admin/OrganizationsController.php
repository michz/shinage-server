<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 20.12.16
 * Time: 17:12
 */

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Organization;
use AppBundle\Service\QuotaCalculator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\ScreenAssociation;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrganizationsController extends Controller
{
    /**
     * @Route("/adm/organizations", name="admin-organizations")
     */
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var EntityRepository $repUsers */
        $repOrgas = $em->getRepository('AppBundle:Organization');

        $orgas = $repOrgas->findAll();
        $quotas = [];

        /** @var QuotaCalculator $quotaCalculator */
        $quotaCalculator = $this->get('app.quotacalculator');

        /** @var Organization $orga */
        foreach ($orgas as $orga) {
            $quotas[$orga->getId()] = $quotaCalculator->getQuotaForOrga($orga);
        }

        return $this->render('adm/organizations.html.twig', [
            'orgas'  => $orgas,
            'quotas' => $quotas,
        ]);
    }
}
