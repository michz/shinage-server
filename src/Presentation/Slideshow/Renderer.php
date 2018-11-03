<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Slideshow;

use App\Entity\Presentation;
use App\Presentation\PresentationRendererInterface;
use App\Presentation\SettingsReaderInterface;
use App\Presentation\Slideshow\Slides\ImageSlide;
use App\Presentation\Slideshow\Slides\VideoSlide;
use Symfony\Component\Asset\Packages;

class Renderer implements PresentationRendererInterface
{
    /** @var SettingsReaderInterface */
    private $settingsReader;

    /** @var Packages */
    private $assetPackages;

    public function __construct(
        SettingsReaderInterface $settingsReader,
        Packages $assetPackages
    ) {
        $this->settingsReader = $settingsReader;
        $this->assetPackages = $assetPackages;
    }

    public function render(Presentation $presentation): string
    {
        /** @var Settings $parsedSettings */
        $parsedSettings = $this->settingsReader->get($presentation->getSettings());
        $count = count($parsedSettings->getSlides());

        // @TODO diese Dateien sind schon weitgehend per Gulp vorbereitet
        $jqueryUrl = $this->assetPackages->getUrl('js/lib/jquery-3.1.1.min.js');
        $revealCssUrl = $this->assetPackages->getUrl('reveal.js/css/reveal.css');
        $revealThemeUrl = $this->assetPackages->getUrl('css/reveal_theme_very_black.css');
        $revealHeadUrl = $this->assetPackages->getUrl('reveal.js/lib/js/head.min.js');
        $revealUrl = $this->assetPackages->getUrl('reveal.js/js/reveal.js');

        $slides = '';

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
                $uniqueId = 'video-' . random_int(1, 10000); // @TODO better unique id (counter)
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
        <script src='$jqueryUrl' type='text/javascript'></script>
        <link rel='stylesheet' href='$revealCssUrl' id='theme'>      
        <link rel='stylesheet' href='$revealThemeUrl' id='theme'>      
        <script src='$revealHeadUrl' type='text/javascript'></script>
        <script src='$revealUrl' type='text/javascript'></script>
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

    public function getLastModified(Presentation $presentation): \DateTime
    {
        return $presentation->getLastModified();
    }
}
