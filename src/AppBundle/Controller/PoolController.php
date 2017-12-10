<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  17:47
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\FilePool;
use AppBundle\Service\FilePoolPermissionChecker;
use AppBundle\Service\FilePoolUrlBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PoolController extends Controller
{

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @Route("/pool/{userRoot}/{path}", name="pool-get", requirements={"userRoot": "[^/]*", "path": ".*"})
     */
    public function getAction(Request $request, string $userRoot, string $path)
    {
        // @TODO Check if file exists

        // @TODO get user that is logged in via cookie
        // @TODO                         or via api token

        $user = $this->getLoggedInUser($request);

        if (!$this->checkPermissions($user, $request, $userRoot)) {
            throw new AccessDeniedException();
        }

        // @TODO If no user is logged in, get connected screen
        //       and check if screen has a presentation scheduled that contains this file

        // @TODO Check if belongs to user that is logged in via cookie
        // @TODO    OR if belongs to user that is logged in via API Token
        // @TODO    OR if belongs to organziation that user belongs to that is logged cookie
        // @TODO    OR if belongs to organziation that user belongs to that is logged in via API Token

        /** @var FilePoolUrlBuilder $poolUrlBuilder */
        $poolUrlBuilder = $this->get('app.filepool.url_builder');
        $absolutePath = $poolUrlBuilder->getAbsolutePath($path, $userRoot);

        $fileInfo = new File($absolutePath);
        $response = new StreamedResponse(
            function () use ($absolutePath) {
                readfile($absolutePath);
            },
            200,
            [
                'Content-Type' => $fileInfo->getMimeType()
            ]
        );
        return $response;
    }

    protected function checkPermissions(User $user, Request $request, $userRoot)
    {
        /** @var FilePoolPermissionChecker $permissionChecker */
        $permissionChecker = $this->get('app.filepool.permission_checker');
        if ($permissionChecker->mayUserAccessRoot($user, $userRoot)) {
            return true;
        }

        return false;
    }

    protected function getLoggedInUser(Request $request)
    {
        return $this->getUser();
    }
}
