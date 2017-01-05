<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\Organization;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Config\Definition\Exception\Exception;


class FilePool
{
    var $base = '';

    public function __construct($basepath)
    {
        $this->base = $basepath;

        // test if path exists
        if (!is_dir($basepath)) {
            mkdir($basepath, 0700, true);
        }
        // if not: try to create
        // test if it exists now; if not: Exception
        if (!is_dir($basepath)) {
            throw new Exception('FilePool path does not exist and cannot be created.');
        }

    }

    public function getUserPaths(User $user) {
        $paths = array();
        $paths['me'] = $this->getPathForUser($user);

        $orgas = $user->getOrganizations();
        foreach ($orgas as $o) { /** @var Organization $o */
            $paths['Org: '.$o->getName()] = $this->getPathForOrga($o);
        }

        return $paths;
    }

    public function getPathForUser(User $user) {
        $path = realpath($this->base) . '/user-' . $user->getId();
        self::createPathIfNeeded($path);
        return $path;
    }

    public function getPathForOrga(Organization $organization) {
        $path = realpath($this->base) . '/orga-' . $organization->getId();
        self::createPathIfNeeded($path);
        return $path;
    }

    public function getFileTree($base, $displayHidden = false) {
        $filename = substr($base, strrpos($base, '/')+1);

        $dir = new PoolDirectory($filename, $base);
        $files = &$dir->getContents();

        if ($handle = opendir($base)) {
            while (false !== ($entry = readdir($handle))) {
                // ignore . and ..
                if ($entry == '.' || $entry == '..') continue;

                // ignore hidden files
                if (!$displayHidden && substr($entry, 0, 1) == '.') continue;

                if (is_dir($base . '/' . $entry)) {
                    $files[] = $this->getFileTree($base . '/' . $entry, $displayHidden);
                }
                elseif (is_file($base . '/' . $entry)) {
                    $files[] = new PoolFile($entry, $base . '/' . $entry);
                }
            }
            closedir($handle);
        }
        return $dir;
    }

    protected static function createPathIfNeeded($path) {
        if (!is_dir($path)) {
            mkdir($path, 0700);
        }
    }


    /*
    public function getScreensForUser(User $user)
    {
        $r = array();

        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('user_id' => $user->getId()));

        foreach ($assoc as $a) {
            $r[] = $a->getScreen();
        }

        // get organizations for user
        $orgas = $user->getOrganizations();

        foreach ($orgas as $o) {
            $assoc = $rep->findBy(array('orga_id' => $o->getId()));
            foreach ($assoc as $a) {
                $r[] = $a->getScreen();
            }
        }

        return $r;
    }

    public function isScreenAssociated(Screen $screen)
    {
        $rep = $this->em->getRepository('AppBundle:ScreenAssociation');
        $assoc = $rep->findBy(array('screen' => $screen->getGuid()));

        return (count($assoc) > 0);
    }
    */
}

abstract class PoolItem {
    const TYPE_DIRECTORY    = 'dir';
    const TYPE_FILE         = 'file';

    protected $name         = '';
    protected $fullpath     = '';

    public function __construct($name, $path) {
        $this->name = $name;
        $this->fullpath = $path;
    }

    public function getName() {
        return $this->name;
    }

    public function getFullPath() {
        return $this->fullpath;
    }

    public abstract function getType();
}

class PoolDirectory extends PoolItem {

    protected $contents = array();

    public function __construct($filename, $fullpath)
    {
        parent::__construct($filename, $fullpath);
    }

    public function &getContents() {
        return $this->contents;
    }

    public function getType() {
        return PoolItem::TYPE_DIRECTORY;
    }
}


class PoolFile extends PoolItem {

    public function __construct($filename, $fullpath)
    {
        parent::__construct($filename, $fullpath);
    }

    public function getType() {
        return PoolItem::TYPE_FILE;
    }
}

