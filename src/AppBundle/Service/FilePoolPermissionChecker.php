<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  18:23
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

class FilePoolPermissionChecker
{
    /** @var FilePool */
    private $filePool;

    /**
     * FilePoolPermissionChecker constructor.
     *
     * @param FilePool $filePool
     */
    public function __construct(FilePool $filePool)
    {
        $this->filePool = $filePool;
    }

    /**
     * @param User   $user
     * @param string $root
     *
     * @return bool
     */
    public function mayUserAccessRoot(User $user, string $root)
    {
        if ($root === 'user-'.$user->getId()) {
            return true;
        }

        foreach ($user->getOrganizations() as $organization) {
            if ($root === 'orga-'.$organization->getId()) {
                return true;
            }
        }

        return false;
    }
}
