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
use AppBundle\Entity\Organization;
use AppBundle\Service\FilePool;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;
use AppBundle\Entity\User;
use AppBundle\Entity\Slide;
use AppBundle\Exceptions\SlideTypeNotImplemented;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PresentationBuilder extends Controller
{

    /**
     * @Route("/manage/presentations", name="management-presentations")
     */
    public function managePresentationsAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $pool = $this->get('app.filepool'); /** @var FilePool $pool */

        $ul_tree = '<ul class="tree-dir">';
        $paths = $pool->getUserPaths($user);
        foreach ($paths as $name => $p) {
            $filename = substr($p, strrpos($p, '/')+1);
            $tree = $pool->getFileTree($p);
            $ul_tree .= "<li>" . $filename . $this->buildFileTree($tree, $filename) . "</li>";
        }
        $ul_tree .= '</ul>';

        $pres = $this->getPresentationsForUser($user);

        return $this->render('manage/presentations/pres-main.html.twig', [
            'file_tree' => $ul_tree,
            'presentations' => $pres,
        ]);
    }

    /**
     * @Route("/manage/presentations/get", name="management-presentations-get")
     */
    public function managePresentationsGetAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $pres = $em->find('\AppBundle\Entity\Presentation', $id);

        // check if user is allowed to see presentation
        if (!$user->isPresentationAllowed($pres)) {
            throw new AccessDeniedException();
        }

        return $this->json($pres);
    }

    /**
     * @Route("/manage/presentations/delete", name="management-presentations-delete")
     */
    public function managePresentationsDeleteAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $id = $request->get('id');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var \AppBundle\Entity\Presentation $pres */
        $pres = $em->find('\AppBundle\Entity\Presentation', $id);

        // check if user is allowed to see presentation
        if (!$user->isPresentationAllowed($pres)) {
            throw new AccessDeniedException();
        }

        // delete slides
        $slides = $pres->getSlides();
        foreach ($slides as $s) {
            $em->remove($s);
        }

        // delete scheduled items
        $rep = $em->getRepository('AppBundle:ScheduledPresentation');
        $scheduled = $rep->findBy(array('presentation' => $pres));
        foreach ($scheduled as $s) {
            $em->remove($s);
        }


        // TODO{s:1} nur flag "deleted" setzen, damit wiederherstellbar
        $em->remove($pres);
        $em->flush();

        return $this->json($pres);
    }

    /**
     * @Route("/manage/presentations/get-thumbnail/{file}", name="management-presentations-get-thumbnail",
     *     requirements={"file": ".*"})
     */
    public function managePresentationsGetThumbnailAction(Request $request, $file)
    {
        // check if user is allowed to see file
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!$user->isPoolFileAllowed($file)) {
            throw new AccessDeniedException();
        }

        // TODO{s:4} generate thumbnail

        // send file
        $response = new Response();
        $file = new File($this->container->getParameter('path_pool') . '/' . $file);

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent(file_get_contents($file->getRealPath()));
        return $response;
    }


    /**
     * @Route("/manage/presentations/set-order", name="management-presentations-set-order")
     */
    public function managePresentationsSetOrder(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $mime_image = array('image/png', 'image/jpg', 'image/jpeg', 'image/gif');
        $em = $this->getDoctrine()->getManager();

        $slides = $request->get('slides');

        foreach ($slides as $o) {
            if ($o['id'] == 0) {
                // new slide detected!
                $file_path = $o['file_path'];
                $presentation = $em->find('\AppBundle\Entity\Presentation', $o['presentation']);

                // get file information
                $file = new File($this->container->getParameter('path_pool') . $file_path);
                if (in_array($file->getMimeType(), $mime_image)) {
                    $type = 'image';
                } else {
                    throw new SlideTypeNotImplemented();
                }

                $slide = new Slide();
                $slide->setSlideType($type);
                $slide->setFilePath($file_path);
                $slide->setSortOrder($o['sort_order']);
                $slide->setDuration($o['duration']);
                $slide->setPresentation($presentation);

                // check if user is allowed to view/edit slides of this presentation
                if (!$user->isSlideAllowed($slide)) {
                    throw new AccessDeniedException();
                }

                $em->persist($slide);
            } else {
                $slide = $em->find('\AppBundle\Entity\Slide', $o['id']); /** @var Slide $slide */

                // check if user is allowed to view/edit slides of this presentation
                if (!$user->isSlideAllowed($slide)) {
                    throw new AccessDeniedException();
                }

                $slide->setSortOrder($o['sort_order']);
                $em->persist($slide);
            }
        }

        $em->flush();
        return $this->json([]);
    }


    /**
     * @Route("/manage/presentations/change-slide", name="management-presentations-change-slide")
     */
    public function managePresentationsChangeSlide(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $slide = $em->find('\AppBundle\Entity\Slide', $request->get('id')); /** @var Slide $slide */

        // check if user is allowed to access slide
        if (!$user->isSlideAllowed($slide)) {
            throw new AccessDeniedException();
        }

        $slide->setDuration($request->get('duration'));
        $em->persist($slide);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }


    /**
     * @Route("/manage/presentations/create", name="management-presentations-create")
     */
    public function managePresentationsCreate(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $presentation = new Presentation();
        $presentation->setTitle($request->get('title'));
        $presentation->setOwnerUser($user);

        $em->persist($presentation);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }


    /**
     * @Route("/manage/presentations/delete-slide", name="management-presentations-delete-slide")
     */
    public function managePresentationsDeleteSlide(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $slide = $em->find('\AppBundle\Entity\Slide', $request->get('id')); /** @var Slide $slide */

        // check if user is allowed to access slide
        if (!$user->isSlideAllowed($slide)) {
            throw new AccessDeniedException();
        }

        $em->remove($slide);
        $em->flush();

        return $this->json(['status' => 'ok']);
    }


    public function getPresentationsForUser(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        return $user->getPresentations($em);
    }




    protected function buildFileTree(PoolDirectory $dir, $base = '')
    {
        $r = '<ul class="tree-dir">';
        $contents = $dir->getContents();
        foreach ($contents as $c) { /** @var PoolItem $c */
            if ($c->getType() == PoolItem::TYPE_DIRECTORY) {
                $r .= '<li>' .
                    $c->getName() .
                    $this->buildFileTree($c, $base . '/' . $c->getName()) .
                    "</li>\n";
            }
        }

        foreach ($contents as $c) { /** @var PoolItem $c */
            if ($c->getType() == PoolItem::TYPE_FILE) {
                $r .= '<li data-jstree=\'{"icon":"jstree-file"}\' data-poolpath=\''. $base . '/' .
                    $c->getName() . '\'>' .
                    $c->getName() .
                    "</li>\n";
            }
        }

        $r .= '</ul>';

        return $r;
    }
}
