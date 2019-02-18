<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Splash;

use App\Entity\PresentationInterface;
use App\Presentation\PresentationRendererInterface;

class Renderer implements PresentationRendererInterface
{
    public function render(PresentationInterface $presentation): string
    {
        $splashImageBase64 = \base64_encode(
            \file_get_contents(__DIR__ . '/../../Resources/private/img/logo-base-dark.png')
        );

        $connectInstructions = '';
        try {
            $settings = \json_decode($presentation->getSettings());
            if (isset($settings->connectCode)) {
                $connectInstructions = '<div id="connect-instructions">Verbindungskennung: ' . $settings->connectCode . '</div>';
            }
        } catch (\Throwable $exception) {
            // If no valid settings could be parsed, there should no connect code be displayed
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
            background: #000;
            color: #fff;
            font-family: sans-serif;
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
        
        #container img {
            display: block;
            margin: 30vh auto 0 auto;
            max-width: 50vw;
        }
        
        #container #connect-instructions {
            position: absolute;
            bottom: 20vh;
            width: 100%;
            font-size: 4vh;
            text-align: center;
            color: #ddd;
        }
        #container #connect-instructions.top {
            top: 20vh;
            bottom: unset;
        }
      
      </style>
    </head>
    <body>
        <div id='container'>
            <img id='logo' src='data:image/png;base64,{$splashImageBase64}'>
            {$connectInstructions}
        </div>
        
        <script>
            var animationTimout = 15000;

            function reposition() {
                var logo = document.getElementById('logo');
                var imgWidth = logo.width;
                var imgHeight = logo.height;
                
                var windowWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
                var windowHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
                
                var maxX = windowWidth - imgWidth;
                var maxY = windowHeight - imgHeight;
                
                var x = Math.floor(Math.random() * maxX);
                var y = Math.floor(Math.random() * maxY);
                
                logo.style.position = 'absolute';
                logo.style.left = x + 'px';
                logo.style.top = y + 'px';
                logo.style.margin = 0;
                
                var instructions = document.getElementById('connect-instructions');
                if (!instructions) {
                } else {
                    if (y > windowHeight / 2) {
                        instructions.className = 'top';
                    } else {
                        instructions.className = '';
                    }
                }
                
                setTimeout(reposition, animationTimout);
            }
            setTimeout(reposition, animationTimout);
        </script>
    </body>
</html>
        ";
    }

    public function getLastModified(PresentationInterface $presentation): \DateTime
    {
        return new \DateTime('@0');
    }
}
