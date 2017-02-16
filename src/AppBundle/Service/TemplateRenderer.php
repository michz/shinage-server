<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 04.02.2017
 * Time: 11:13
 */

namespace AppBundle\Service;

class TemplateRenderer
{
    protected $twig = null;
    protected $templateManager = null;

    public function __construct(\Twig_Environment $twig, TemplateManager $templateManager)
    {
        $this->twig = $twig;
        $this->templateManager = $templateManager;
    }
}
