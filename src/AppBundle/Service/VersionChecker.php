<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 31.12.17
 * Time: 13:26
 */

namespace AppBundle\Service;

class VersionChecker
{
    /**
     * Returns the current installed version.
     *
     * @return string
     */
    public function getVersion()
    {
        $exitCode = 0;
        $returnString = '';

        // try to get exact tag
        $r = exec('git describe --tags --abbrev=0', $returnString, $exitCode);
        if ($exitCode === 0) {
            return $r;
        }

        // otherwise get branch name
        return $this->getBranch() . '#' . $this->getCommit();
    }

    public function getBranch()
    {
        $exitCode = 0;
        $returnString = '';
        $branch = exec('git rev-parse --abbrev-ref HEAD', $returnString, $exitCode);
        if ($exitCode === 0) {
            return $branch;
        }
        return '';
    }

    public function getCommit()
    {
        $exitCode = 0;
        $returnString = '';
        $commit = exec('git rev-parse HEAD', $returnString, $exitCode);
        if ($exitCode === 0) {
            return $commit;
        }
        return '';
    }
}
