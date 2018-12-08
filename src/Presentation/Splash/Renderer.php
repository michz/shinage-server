<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation\Splash;

use App\Entity\PresentationInterface;
use App\Presentation\PresentationRendererInterface;

class Renderer implements PresentationRendererInterface
{
    public function render(PresentationInterface $presentation): string
    {
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
        
        #container, #container iframe {
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
      
      </style>
    </head>
    <body>
        <div id='container'>Shinage -- no presentation configured</div>
    </body>
</html>
        ";
        // @TODO add beautiful default splash screen
    }

    public function getLastModified(PresentationInterface $presentation): \DateTime
    {
        return new \DateTime('@0');
    }
}
