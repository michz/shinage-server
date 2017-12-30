<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  17:47
 */

namespace AppBundle\Controller;

use AppBundle\Service\FilePoolUrlBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PoolController extends Controller
{

    /**
     * @param Request $request
     * @param string  $userRoot
     * @param string  $path
     *
     * @return Response|StreamedResponse
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @Route("/pool/{userRoot}/{path}", name="pool-get", requirements={"userRoot": "[^/]*", "path": ".*"})
     */
    public function getAction(Request $request, string $userRoot, string $path)
    {
        // @TODO get user that is logged in via cookie
        // @TODO                         or via api token

        /*
        $user = $this->getLoggedInUser($request);

        if (!$this->checkPermissions($user, $request, $userRoot)) {
            return new Response('', 403);
            //throw new AccessDeniedException();
        }
        */

        // @TODO If no user is logged in, get connected screen
        //       and check if screen has a presentation scheduled that contains this file

        // @TODO Check if belongs to user that is logged in via cookie
        // @TODO    OR if belongs to user that is logged in via API Token
        // @TODO    OR if belongs to organziation that user belongs to that is logged cookie
        // @TODO    OR if belongs to organziation that user belongs to that is logged in via API Token

        /** @var FilePoolUrlBuilder $poolUrlBuilder */

        try {
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
        } catch (FileNotFoundException $oException) {
            return new Response('', 404);
        } catch (AccessDeniedException $oException) {
            return new Response('', 403);
        }
    }

//    /**
//     * @param User|null $user
//     * @param Request   $request
//     * @param           $userRoot
//     *
//     * @return bool
//     */
//    protected function checkPermissions($user, Request $request, $userRoot): bool
//    {
//        // Check logged in user
//        if ($user !== null) {
//            /** @var FilePoolPermissionChecker $permissionChecker */
//            $permissionChecker = $this->get('app.filepool.permission_checker');
//            if ($permissionChecker->mayUserAccessRoot($user, $userRoot)) {
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    protected function getLoggedInUser(Request $request)
//    {
//        return $this->getUser();
//    }
}
