<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Service\PresentationBuilders;

use App\Entity\PresentationInterface;

/**
 * @deprecated
 */
class MirrorPresentation
{
    const PRESENTATION_TYPE = 'mirror';

    public function supports(PresentationInterface $presentation): bool
    {
        return self::PRESENTATION_TYPE === $presentation->getType();
    }

    /**
     * @return string[]|array
     */
    public function getSupportedTypes(): array
    {
        return [self::PRESENTATION_TYPE];
    }

    /**
     * @return \App\Entity\ScreenRemote\PlayablePresentation|bool|string
     */
    public function buildPresentation(PresentationInterface $presentation)
    {
        $settings = json_decode($presentation->getSettings());
        $this->checkValid($presentation);

        $url = $settings->url;
        if (isset($settings->type) && 'jsonp' === $settings->type) {
            $url .= '?callback=REPLACE_JSONP_CALLBACK_DUMMY';
        }

        return file_get_contents($url);
    }

    public function getLastModified(PresentationInterface $presentation): \DateTime
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

    protected function checkValid(PresentationInterface $presentation): void
    {
        $settings = json_decode($presentation->getSettings());
        if (!isset($settings->url)) {
            throw new \Exception('Presentation invalid.');
        }
    }
}
