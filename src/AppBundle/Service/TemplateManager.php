<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 04.02.2017
 * Time: 11:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\PresentationTemplate;

class TemplateManager
{
    /** @var string */
    protected $templatePoolPath = '';

    /** @var array */
    protected $templates = null;

    public function __construct($templatePoolPath)
    {
        $this->templatePoolPath;

        $this->loadTemplates();
    }

    protected function loadTemplates()
    {
        $directories = $this->getTemplateDirectories();
        foreach ($directories as $directory) {
            $template = $this->loadTemplate($this->templatePoolPath.'/'.$directory);
        }
    }

    protected function getTemplateDirectories()
    {
        $directories = [];
        if ($handle = opendir($this->templatePoolPath)) {
            while (false !== ($entry = readdir($handle))) {
                // ignore . and ..
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                // ignore hidden files and directories
                if (substr($entry, 0, 1) == '.') {
                    continue;
                }

                if (is_dir($this->templatePoolPath . '/' . $entry)) {
                    $directories[] = $entry;
                }
            }
            closedir($handle);
        }
        return $directories;
    }

    protected function loadTemplate($directory)
    {
        //@TODO look for template.php
    }

    public function getTemplates()
    {
        //@TODO
    }
}
