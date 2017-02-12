<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 12.02.2017
 * Time: 16:58
 */

namespace AppBundle\Service;

use AppBundle\Entity\Organization;
use AppBundle\Entity\QuotaCalculated;
use AppBundle\Entity\User;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class QuotaCalculator
{
    /** @var FilePool */
    protected $filePool = null;

    /** @var FilesystemAdapter */
    protected $cache = null;


    public function __construct(FilePool $filePool, FilesystemAdapter $cache)
    {
        $this->filePool = $filePool;
        $this->cache = $cache;
    }


    /**
     * @param User $user
     * @return QuotaCalculated
     */
    public function getQuotaForUser(User $user)
    {
        $base = $this->filePool->getPathForUser($user);
        $used = $this->getDirectorySizeRecursive($base);
        $quota = new QuotaCalculated();
        $quota->setUsedAbsolute($used);
        return $quota;
    }

    /**
     * @param Organization $orga
     * @return QuotaCalculated
     */
    public function getQuotaForOrga(Organization $orga)
    {
        $base = $this->filePool->getPathForOrga($orga);
        $used = $this->getDirectorySizeRecursive($base);
        $quota = new QuotaCalculated();
        $quota->setUsedAbsolute($used);
        return $quota;
    }

    protected function getDirectorySizeRecursive($base)
    {
        $tree = $this->filePool->getFileTree($base, true);
        return $this->getFileSize($tree);
    }

    protected function getFileSize(PoolItem $item)
    {
        if ($item->getType() == PoolItem::TYPE_FILE) {
            return filesize($item->getFullPath());
        } else {
            $s = 0;
            foreach ($item->getContents() as $content) {
                $s += $this->getFileSize($content);
            }
            return $s;
        }
    }
}
