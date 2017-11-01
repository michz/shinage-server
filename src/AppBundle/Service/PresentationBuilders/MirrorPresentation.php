<?php

namespace AppBundle\Service\PresentationBuilders;

use AppBundle\Entity\Presentation;

/**
 * @author   :  Michael Zapf <m.zapf@mtx.de>
 * @date     :  27.10.17
 * @time     :  10:46
 */
class MirrorPresentation implements PresentationBuilderInterface
{
    const PRESENTATION_TYPE = 'mirror';

    public function supports(Presentation $presentation)
    {
        return ($presentation->getType() === self::PRESENTATION_TYPE);
    }

    public function buildPresentation(Presentation $presentation)
    {
        $settings = json_decode($presentation->getSettings());
        $this->checkValid($presentation);

        $url = $settings->url;
        if (isset($settings->type) && $settings->type === 'jsonp') {
            $url .= '?callback=REPLACE_JSONP_CALLBACK_DUMMY';
        }

        return file_get_contents($url);
    }

    public function getLastModified(Presentation $presentation)
    {
        $settings = json_decode($presentation->getSettings());
        $this->checkValid($presentation);

        $client = new \GuzzleHttp\Client();
        $response = $client->head($settings->url);
        $lastModifiedRaw = $response->getHeader('Last-Modified');
        if (empty($lastModifiedRaw)) {
            return new \DateTime('now');
        }
        return new \DateTime($lastModifiedRaw[0]);
    }

    protected function checkValid(Presentation $presentation)
    {
        $settings = json_decode($presentation->getSettings());
        if (!isset($settings->url)) {
            throw new \Exception('Presentation invalid.');
        }
    }
}
