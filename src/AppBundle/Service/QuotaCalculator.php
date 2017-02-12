<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 12.02.2017
 * Time: 16:58
 */

namespace AppBundle\Service;

use AppBundle\Entity\Interfaces\Ownable;
use AppBundle\Entity\Interfaces\Owner;
use AppBundle\Entity\Organization;
use AppBundle\Entity\QuotaCalculated;
use AppBundle\Entity\User;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

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
        $cacheId = $this->getCacheId($user);

        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($cacheId);

        if (!$cacheItem->isHit()) {
            $base = $this->filePool->getPathForUser($user);
            $used = $this->getDirectorySizeRecursive($base);
            $quota = new QuotaCalculated();
            $quota->setUsedAbsolute($used);
            $cacheItem->set($quota);
            $cacheItem->tag($cacheId);
        }

        return $cacheItem->get();
    }

    /**
     * @param Organization $orga
     * @return QuotaCalculated
     */
    public function getQuotaForOrga(Organization $orga)
    {
        $cacheId = $this->getCacheId($orga);

        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($cacheId);

        if (!$cacheItem->isHit()) {
            $base = $this->filePool->getPathForOrga($orga);
            $used = $this->getDirectorySizeRecursive($base);
            $quota = new QuotaCalculated();
            $quota->setUsedAbsolute($used);
            $cacheItem->set($quota);
            $cacheItem->tag($cacheId);
        }

        return $cacheItem->get();
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

    protected function getCacheId(Owner $owner)
    {
        return str_replace(':', '-', $owner->getOwnerString() . ':quota');
    }
}
