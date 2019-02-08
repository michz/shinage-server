<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow;

use App\Entity\PresentationInterface;
use App\Presentation\PresentationRendererInterface;
use App\Presentation\SettingsReaderInterface;
use App\Presentation\Slideshow\Slides\ImageSlide;
use App\Presentation\Slideshow\Slides\VideoSlide;
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
        $count = \count($parsedSettings->getSlides());

        $playerUrl = $this->assetPackages->getUrl('assets/player.min.js');
        $playerCssUrl = $this->assetPackages->getUrl('assets/player.min.css');

        $slides = '';
        $videoCounter = 0;

        foreach ($parsedSettings->getSlides() as $slide) {
            // @TODO Own render service per Slide Type
            if ('Image' === $slide->getType()) {
                /* @var $slide ImageSlide */
                $slides .= "
                    <section 
                        data-autoslide='{$slide->getDuration()}' 
                        data-background-image='{$slide->getSrc()}'
                        data-background-size='contain'
                        >
                    </section>
                ";
            } elseif ('Video' === $slide->getType()) {
                /* @var $slide VideoSlide */
                ++$videoCounter;
                $uniqueId = 'video-' . $videoCounter++; // @TODO better unique id (counter)
                $slides .= "
                    <section 
                        id='{$uniqueId}'
                        data-background-video='{$slide->getSrc()}'
                        data-background-size='contain'
                        >
                        <!--
                        <video id='{$uniqueId}' src='{$slide->getSrc()}'></video>
                        -->
                    </section>
                ";
            }
        }

        $slides = $this->replaceVirtualUrls($slides);

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
        <!-- Slide count: $count -->
            <div class='reveal'>
                <div class='slides'>
                    $slides
                </div>
            </div>
        </div>
        <script>
            Reveal.initialize({
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
                autoSlide: 0,
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
            });
            
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
}
