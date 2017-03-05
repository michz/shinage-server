<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 04.02.2017
 * Time: 11:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\PresentationTemplate;
use Twig_Environment;
use Twig_Loader_Filesystem;

class TemplateRenderer
{
    protected $twig = null;
    protected $templateManager = null;

    public function __construct(\Twig_Environment $twig, TemplateManager $templateManager)
    {
        $this->twig = $twig;
        $this->templateManager = $templateManager;
    }

    /**
     * @param PresentationTemplate $template
     * @param array                $options
     * @return mixed|string
     */
    public function render(PresentationTemplate $template, $options)
    {
        $loader = new Twig_Loader_Filesystem($template->getBasePath());
        $twig = new Twig_Environment($loader, []);
        $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) {
            // @TODO implement whatever logic you need to determine the asset path

            return sprintf('../assets/%s', ltrim($asset, '/'));
        }));
        $twig->addFunction(new \Twig_SimpleFunction('path', function ($asset) {
            // @TODO implement whatever logic you need to determine the asset path

            return '';
        }));

        return $twig->render($template->getTemplateFile(), $options);
    }
}
