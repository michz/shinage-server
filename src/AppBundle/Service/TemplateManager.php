<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 04.02.2017
 * Time: 11:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\PresentationTemplate;
use Symfony\Component\ClassLoader\MapClassLoader;

class TemplateManager
{

    /** @var string */
    protected $templatePoolPath = '';

    /** @var array */
    protected $templates = null;

    public function __construct($templatePoolPath)
    {
        $this->templatePoolPath = $templatePoolPath;

        $this->loadTemplates();
    }

    protected function loadTemplates()
    {
        $directories = $this->getTemplateDirectories();
        $this->createAutoloader($directories);
        foreach ($directories as $directory) {
            $realpath = realpath($this->templatePoolPath);
            $template = $this->loadTemplate('ShinageTemplates\\BuiltIn\\'.$directory, $realpath.'/'.$directory);
            $this->templates['ShinageTemplates\\BuiltIn\\'.$directory] = $template;
        }
    }

    protected function createAutoloader($directories)
    {
        $classes = [];
        foreach ($directories as $directory) {
            $realpath = realpath($this->templatePoolPath.'/'.$directory);
            $classes['ShinageTemplates\\BuiltIn\\'.$directory] = $realpath.'/'.$directory.'.php';
        }

        $loader = new MapClassLoader($classes);
        $loader->register();
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

    protected function loadTemplate($name, $directory)
    {
        return new $name($directory);
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param string $name
     * @return PresentationTemplate|null
     */
    public function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }
        return null;
    }
}
