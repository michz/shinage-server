<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  12.02.17
 * @time     :  18:13
 */

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatBytes', array($this, 'byteFilter')),
        );
    }

    public function byteFilter($size, $precision = 1)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[(int)floor($base)];
    }


    public function getName()
    {
        return 'app_extension';
    }
}
