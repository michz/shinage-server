<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Controller\Account;

use App\Entity\Api\AccessKey;
use App\Entity\User;
use App\Form\ApiKeyForm;
use App\UserType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

class AccountController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        UserManagerInterface $userManager,
        TranslatorInterface $translator,
        FormFactoryInterface $formFactory,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->encoderFactory = $encoderFactory;
    }

    public function indexAction(): RedirectResponse
    {
        return $this->redirectToRoute('account-edit');
    }

    public function editAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $form = $this->formFactory->createNamedBuilder('form1name', FormType::class, $user)
            ->add('email', EmailType::class)
            //->add('password', PasswordType::class)
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        $form_pw = $this->formFactory->createNamedBuilder('form2name', FormType::class, $user)
            ->add('old-password', PasswordType::class, ['label'=>'oldPassword', 'mapped' => false])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'PasswordsMustMatch',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'PasswordAgain'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();

        // build default AccessKey
        $newApiKey = new AccessKey();
        $newApiKey->setOwner($user);

        // build AccessKey-form and handle submission
        $formApiKeyBuilder = $this->formFactory->createNamedBuilder('form3name', ApiKeyForm::class, $newApiKey);
        $createApiKeyForm = $formApiKeyBuilder->getForm();
        $this->handleCreateApiToken($request, $createApiKeyForm);

        if ('POST' === $request->getMethod()) {
            if ($request->request->has('form1name')) {
                $form->handleRequest($request);

                // @TODO Localize Flash messages
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        $this->userManager->updateUser($user, true);
                        $this->addFlash('success', 'Die Änderungen wurden gespeichert.');
                    } else {
                        $this->addFlash(
                            'error',
                            'Die Änderungen konnten leider nicht gespeichert werden. ' .
                            'Evtl. sind Eingaben nicht korrekt.'
                        );
                    }
                }
            }

            if ($request->request->has('form2name')) {
                $form_pw->handleRequest($request);

                if ($form_pw->isSubmitted()) {
                    $encoder = $this->encoderFactory->getEncoder($user);

                    if (!$encoder->isPasswordValid(
                        $user->getPassword(),
                        $form_pw->get('old-password')->getData(),
                        $user->getSalt()
                    )) {
                        // wrong old password
                        $form_pw->get('old-password')->addError(
                            new FormError($this->translator->trans('WrongOldPassword'))
                        );
                    } else {
                        if ($form_pw->isValid()) {
                            // now check if it is empty
                            $pw = $form_pw->get('plainPassword')->getData();
                            if (empty($pw)) {
                                $this->addFlash('error', 'Das Passwort darf nicht leer sein.');
                            } else {
                                // everythin seems ok, now set password and save
                                $user->setPlainPassword($pw);

                                $this->userManager->updateUser($user, true);
                                $this->addFlash('success', 'Das Passwort wurde erfolgreich geändert.');
                            }
                        }
                    }
                }
            }
        }

        // get API keys
        $rep = $this->entityManager->getRepository('App:Api\AccessKey');
        $apiKeys = $rep->findBy(['owner' => $user]);

        return $this->render('account/user.html.twig', [
            'form'                  => $form->createView(),
            'form_pw'               => $form_pw->createView(),
            'form_add_api_key'      => $createApiKeyForm->createView(),
            'api_keys'              => $apiKeys,
        ]);
    }

    public function orgaAction(Request $request): Response
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $orga_new = new User();
        $orga_new->setUserType(UserType::USER_TYPE_ORGA);

        $form_create = $this->createFormBuilder($orga_new)
            ->add('Name', TextType::class, ['trim' => true])
            ->add('email', EmailType::class, ['label' => 'E-Mail'])
            ->add('save', SubmitType::class, ['label' => 'Save'])
            ->getForm();
        $form_create->handleRequest($request);
        if ($form_create->isSubmitted()) {
            if ($form_create->isValid()) {
                $orga_new->setUsername($orga_new->getName());
                $orga_new->setPassword('');
                $orga_new->setPlainPassword('');
                try {
                    $this->userManager->updateUser($orga_new, true);
                    $this->entityManager->flush();
                    $user->addOrganization($orga_new);
                    $this->userManager->updateUser($user);
                    $this->entityManager->flush();
                    $this->entityManager->refresh($orga_new); // needed to notify $user that he is in a new organization
                    $this->addFlash('success', 'Die neue Organisation wurde gespeichert.');
                } catch (UniqueConstraintViolationException $ex) {
                    $this->addFlash(
                        'error',
                        'Der gewählte Name oder die E-Mail-Adresse wird bereits verwendet. ' .
                        'Bitte probiere es mit einer anderen Kombination.'
                    );
                    $this->getDoctrine()->resetManager();
                }
            } else {
                $this->addFlash('error', 'Die Organisation konnte leider nicht angelegt werden.');
            }
        }

        $orgas = $user->getOrganizations();
        return $this->render('account/organizations.html.twig', [
            'form_create' => $form_create->createView(),
            'organizations' => $orgas,
        ]);
    }

    public function orgaLeaveAction(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $organization = $this->entityManager->find(User::class, $id);
        $user->removeOrganization($organization);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Die Organisation wurde verlassen.');
        return $this->redirectToRoute('account-organizations');
    }

    public function orgaAddUserAction(Request $request): RedirectResponse
    {
        /** @var User $user */
        /** @var User $user_new */
        $user = $this->tokenStorage->getToken()->getUser();
        $rep = $this->entityManager->getRepository('App:User');
        $user_new = $rep->findOneBy(['email' => $request->get('email')]);
        $orga = $this->entityManager->find(User::class, $request->get('organization'));

        // check if user is allowed to edit organization
        if (!$user->getOrganizations()->contains($orga)) {
            $this->addFlash('error', 'Sie dürfen diese Organisation leider nicht bearbeiten.');
            return $this->redirectToRoute('account-organizations');
        }

        if (!$user_new) {
            $this->addFlash('error', 'Es wurde kein Benutzer mit der angegebenen E-Mail-Adresse gefunden.');
            return $this->redirectToRoute('account-organizations');
        }

        if ($user_new->getOrganizations()->contains($orga)) {
            $this->addFlash('notice', 'Der Benutzer (' . $user_new->getEmail() .
                ') ist bereits Mitglied der Organisation (' . $orga->getName() . ').');
            return $this->redirectToRoute('account-organizations');
        }

        $user_new->addOrganization($orga);

        $this->entityManager->persist($user_new);
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            'Der Benutzer (' . $user_new->getEmail() .
            ') wurde der Organisation (' . $orga->getName() . ') hinzugefügt.'
        );
        return $this->redirectToRoute('account-organizations');
    }

    public function orgaRemoveAction(
        int $orga_id,
        int $user_id
    ): RedirectResponse {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $organization = $this->entityManager->find(User::class, $orga_id);
        $user_other = $this->entityManager->find(User::class, $user_id);

        // check if user is allowed to edit organization
        if (!$user->getOrganizations()->contains($organization)) {
            $this->addFlash('error', 'Sie dürfen diese Organisation leider nicht bearbeiten.');
            return $this->redirectToRoute('account-organizations');
        }

        $user_other->removeOrganization($organization);
        $this->entityManager->persist($user_other);
        $$this->entityManagerem->flush();

        $this->addFlash('success', 'Die Benutzer (' . $user_other->getEmail() .
            ') wurde aus der Organisation (' . $organization->getName() . ') entfernt.');
        return $this->redirectToRoute('account-organizations');
    }

    public function deleteApiKeyAction(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $key = $this->entityManager->find('App:Api\AccessKey', $id);
        if ($key->getOwner() === $user || $user->getOrganizations()->contains($key->getOwner())) {
            $this->entityManager->remove($key);
            $this->entityManager->flush();

            $this->addFlash('success', 'Der Schlüssel (' . $key->getCode() . ') wurde gelöscht.');
        } else {
            $this->addFlash('error', 'Der Schlüssel (' . $key->getCode() . ') konnte leider nicht gelöscht werden.');
        }

        // Redirect to referer if possible
        $ref = $request->headers->get('referer');
        if (empty($ref)) {
            return $this->redirectToRoute('account-edit');
        }

        return $this->redirect($ref);
    }

    /**
     * Handles the create-api-key submission.
     */
    protected function handleCreateApiToken(Request $request, Form $createApiKeyForm): void
    {
        if ('POST' !== $request->getMethod() || !$request->request->has($createApiKeyForm->getName())) {
            return;
        }

        $createApiKeyForm->handleRequest($request);

        if (!$createApiKeyForm->isSubmitted() || !$createApiKeyForm->isValid()) {
            return;
        }

        /** @var \App\Entity\Api\AccessKey $apiKey */
        $apiKey = $createApiKeyForm->getData();
        $apiKey->generateAndSetCode();

        // @TODO Debug: Standardrollen entfernen und konfigurierbar machen
        $apiKey->setRoles(['FILE_UPLOAD']);

        $this->entityManager->persist($apiKey);
        $this->entityManager->flush();
    }
}
