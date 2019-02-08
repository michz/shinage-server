<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Management\Screens;

use App\Entity\Screen;
use App\Entity\ScreenAssociation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScreenRightsController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function indexAction(Screen $screen): Response
    {
        $repo = $this->entityManager->getRepository(ScreenAssociation::class);
        $associations = $repo->findBy(['screen' => $screen]);

        return $this->render('manage/screens/rights.html.twig', [
            'screen' => $screen,
            'associations' => $associations,
        ]);
    }

    public function removeAction(ScreenAssociation $association): Response
    {
        $screenId = $association->getScreen()->getGuid();
        $this->entityManager->remove($association);
        $this->entityManager->flush();

        return $this->redirectToRoute('management-screen-rights', ['guid' => $screenId]);
    }

    public function addAction(Request $request, Screen $screen): Response
    {
        $user = $this->findUserByMail($request->get('mail'));
        if (null === $user) {
            return new Response('', 400);
        }

        // @TODO check if there is already an association for this user and screen and add just roles

        $screenAssociation = new ScreenAssociation();
        $screenAssociation->setUser($user);
        $screenAssociation->setScreen($screen);
        $screenAssociation->setRoles($request->get('roles'));

        $this->entityManager->persist($screenAssociation);
        $this->entityManager->flush();

        return $this->redirectToRoute('management-screen-rights', ['guid' => $screen->getGuid()]);
    }

    public function checkUserExistsAction(Request $request): Response
    {
        $user = $this->findUserByMail($request->get('mail'));
        if (null === $user) {
            return new Response('', 404);
        }

        return new Response('', 204);
    }

    private function findUserByMail(string $mail): ?User
    {
        $mail = trim($mail);
        $repo = $this->entityManager->getRepository(User::class);
        return $repo->findOneBy(['email' => $mail]);
    }
}
