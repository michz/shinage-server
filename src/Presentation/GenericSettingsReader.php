<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace App\Presentation;

use JMS\Serializer\SerializerInterface;

class GenericSettingsReader implements SettingsReaderInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var string */
    private $settingsType;

    public function __construct(
        SerializerInterface $serializer,
        string $settingsType
    ) {
        $this->serializer = $serializer;
        $this->settingsType = $settingsType;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $serializedSettings)
    {
        return $this->serializer->deserialize($serializedSettings, $this->settingsType, 'json');
    }
}
