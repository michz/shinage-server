<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\RevealJs;

use App\Entity\PresentationInterface;
use App\Presentation\PresentationRendererInterface;
use App\Presentation\SettingsReaderInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Routing\RouterInterface;

class Renderer implements PresentationRendererInterface
{
    const POOL_VIRTUAL_BASE_URL = 'pool://';

    /** @var SettingsReaderInterface */
    private $settingsReader;

    /** @var Packages */
    private $assetPackages;

    /** @var string */
    private $baseUrl;

    public function __construct(
        SettingsReaderInterface $settingsReader,
        Packages $assetPackages,
        RouterInterface $router
    ) {
        $this->settingsReader = $settingsReader;
        $this->assetPackages = $assetPackages;
        $this->baseUrl = $router->generate('pool-get-root', [], RouterInterface::ABSOLUTE_URL);
    }

    public function render(PresentationInterface $presentation): string
    {
        /** @var Settings $parsedSettings */
        $parsedSettings = $this->settingsReader->get($presentation->getSettings());

        $content = $this->replaceVirtualUrls($parsedSettings->getContent());
        $presentationsRevealSettings = \trim($parsedSettings->getRevealSettings());
        $revealSettings = $presentationsRevealSettings ?: $this->getDefaultRevealSettings();

        $playerUrl = $this->assetPackages->getUrl('assets/player.min.js');
        $playerCssUrl = $this->assetPackages->getUrl('assets/player.min.css');

        return "
<!doctype html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta http-equiv='x-ua-compatible' content='ie=edge'>
        <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=no'>
        <title></title>
        <style>
            ::-webkit-scrollbar {
                display: none;
            }
            
            html, body {
                margin: 0;
                padding: 0;
                overflow: hidden;
                height: 100%;
            }
            
            body {
                max-height: 100%;
                float: left;
                width: 100%;
            }
            
            #container {
                display: block;
                margin: 0;
                padding: 0;
                width: 100%;
                min-width: 100%;
                max-width: 100%;
                height: 100%;
                min-height: 100%;
                max-height: 100%;
                overflow: hidden;
            }
            
            #container .reveal section img,
            #container .reveal section video {
                display: block;
                margin: 0;
                padding: 0;
                object-fit: cover;
                width: 100%;
                height: 100%;
                max-width: 100%;
                max-height: 100%;
            }
        </style>
        <script src='$playerUrl' type='text/javascript'></script>
        <link rel='stylesheet' href='$playerCssUrl' id='theme'>      
    </head>
    <body>
        <div id='container'>
            <div class='reveal'>
                <div class='slides'>
                    $content
                </div>
            </div>
        </div>
        <script>
            Reveal.initialize($revealSettings);
            
            document.addEventListener('ended', function() {
                Reveal.next();
            }, true);
        </script>
    </body>
</html>
        ";
    }

    public function getLastModified(PresentationInterface $presentation): \DateTime
    {
        return $presentation->getLastModified();
    }

    private function replaceVirtualUrls(string $data): string
    {
        return str_replace(self::POOL_VIRTUAL_BASE_URL, $this->baseUrl, $data);
    }

    private function getDefaultRevealSettings(): string
    {
        return "{
            width: 1280,
            height: 720,
            controls: false,
            controlsTutorial: false,
            progress: false,
            slideNumber: false,
            history: false,
            keyboard: false,
            overview: false,
            touch: false,
            loop: true,
            rtl: false,
            shuffle: false,
            fragments: false,
            fragmentInURL: false,
    
            // Flags if the presentation is running in an embedded mode,
            // i.e. contained within a limited portion of the screen
            embedded: false,
            help: false,
            showNotes: false,
            autoPlayMedia: true,
            autoSlide: 5000,
            autoSlideStoppable: false,
            autoSlideMethod: Reveal.navigateNext,
            mouseWheel: false,
            hideAddressBar: true,
            previewLinks: false,
            transition: 'none', // none/fade/slide/convex/concave/zoom
            transitionSpeed: 'default', // default/fast/slow
            backgroundTransition: 'none', // none/fade/slide/convex/concave/zoom
            viewDistance: 3,
            parallaxBackgroundImage: '',
            parallaxBackgroundSize: '',
            parallaxBackgroundHorizontal: null,
            parallaxBackgroundVertical: null,
            display: 'block'
        }";
    }
}
