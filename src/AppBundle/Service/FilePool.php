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
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolFile;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Config\Definition\Exception\Exception;

class FilePool
{
    protected $base = '';

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

    public function getUserPaths(User $user)
    {
        $paths = array();
        $paths['me'] = $this->getPathForUser($user);

        $orgas = $user->getOrganizations();
        foreach ($orgas as $o) { /** @var Organization $o */
            $paths['Org: '.$o->getName()] = $this->getPathForOrga($o);
        }

        return $paths;
    }

    public function getPathForUser(User $user)
    {
        $path = realpath($this->base) . '/user-' . $user->getId();
        self::createPathIfNeeded($path);
        return $path;
    }

    public function getPathForOrga(Organization $organization)
    {
        $path = realpath($this->base) . '/orga-' . $organization->getId();
        self::createPathIfNeeded($path);
        return $path;
    }

    public function getFileTree($base, $displayHidden = false)
    {
        $filename = substr($base, strrpos($base, '/')+1);

        $dir = new PoolDirectory($filename, $base);
        $files = &$dir->getContents();

        if ($handle = opendir($base)) {
            while (false !== ($entry = readdir($handle))) {
                // ignore . and ..
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // ignore hidden files
                if (!$displayHidden && substr($entry, 0, 1) == '.') {
                    continue;
                }

                if (is_dir($base . '/' . $entry)) {
                    $files[] = $this->getFileTree($base . '/' . $entry, $displayHidden);
                } elseif (is_file($base . '/' . $entry)) {
                    $files[] = new PoolFile($entry, $base . '/' . $entry);
                }
            }
            closedir($handle);
        }
        return $dir;
    }

    public static function createPathIfNeeded($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0700);
        }
    }
}
