<?php
/**
 *
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  15.02.17
 * @time     :  20:11
 */

namespace AppBundle\Entity;

abstract class PresentationTemplate
{
    protected $templatePath = '';


    abstract public function getDisplayName($language);
    abstract public function getDescription($language);
    abstract public function getAuthor();
    abstract public function getWebsite();
    abstract public function getBasePath();


    public function __construct($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    public function getTemplateFile()
    {
        return 'pres.html.twig';
    }

    /*
    public function getPresentationHtml(Presentation $presentation)
    {
        $htmlPath = $this->templatePath . '/pres.html.twig';
        if (file_exists($htmlPath)) {
            return file_get_contents($htmlPath);
        }
        return '';
    }
    */
}
