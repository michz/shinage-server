<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  10.12.17
 * @time     :  16:39
 */

namespace AppBundle\Service;

use AppBundle\Entity\jstree\FileNode;
use AppBundle\Service\Pool\PoolDirectory;
use AppBundle\Service\Pool\PoolItem;

class JstreeFileTreeBuilder
{
    /** @var FilePool */
    private $filePool;

    /** @var FileNode[] */
    protected $tree = [];

    /**
     * jstreeFileTreeBuilder constructor.
     *
     * @param FilePool   $filePool
     */
    public function __construct(FilePool $filePool)
    {
        $this->filePool = $filePool;
    }

    /**
     * Adds a new root to the tree.
     *
     * @param string $root
     *
     * @return jstreeFileTreeBuilder
     */
    public function addNewRoot(string $root, string $displayedDirName): jstreeFileTreeBuilder
    {
        $tree = $this->filePool->getFileTree($root);

        $root = new FileNode();
        $root->text = $displayedDirName;
        $root->children = $this->getDirectoryNodes($tree);
        $root->state->opened = true;

        $this->tree[] = $root;
        return $this;
    }

    /**
     * @param PoolDirectory $directory
     *
     * @return array
     */
    protected function getDirectoryNodes(PoolDirectory $directory): array
    {
        $fileNodes = [];
        $leaves = [];
        foreach ($directory->getContents() as $file) {
            if ($file->getType() !== PoolItem::TYPE_DIRECTORY) {
                continue;
            }
            $fileNode = new FileNode();
            $fileNode->text = $file->getName();
            $fileNode->children = $this->getDirectoryNodes($file);
            $fileNodes[] = $fileNode;
        }
        return $fileNodes;
    }

    /**
     * Returns the generated tree.
     *
     * @return array
     */
    public function getTree(): array
    {
        return $this->tree;
    }
}
