<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  08.12.17
 * @time     :  07:12
 */

namespace AppBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

class ElFinderInstallerHandler extends ScriptHandler
{

    /**
     * Call the demo command of the Acme Demo Bundle.
     */
    public static function installElFinderAssets()
    {
        $currentDir = getcwd();

        $elFinderDir = $currentDir . '/vendor/studio-42/elfinder/';
        $targetJsDir = $currentDir . '/web/js/elfinder';
        $targetCssDir = $currentDir . '/web/css/elfinder/css';
        $targetImageDir = $currentDir . '/web/css/elfinder/img';

        if (!is_dir($targetJsDir)) {
            mkdir($targetJsDir, 0777, true);
        }
        if (!is_dir($targetCssDir)) {
            mkdir($targetCssDir, 0777, true);
        }
        copy($elFinderDir . '/js/elfinder.min.js', $targetJsDir . '/elfinder.min.js');
        copy($elFinderDir . '/js/i18n/elfinder.de.js', $targetJsDir . '/elfinder.de.js');
        copy($elFinderDir . '/css/elfinder.min.css', $targetCssDir . '/elfinder.min.css');
        self::copyDirectory($elFinderDir . '/img', $targetImageDir);
    }

    private static function copyDirectory(string $src, string $dst)
    {
        $dir = opendir($src);
        if ($dir === false) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be opened.', $dst));
        }
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
