<?php

namespace Pine\Configuration;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

/**
 * Class SerializerFactory
 *
 * @package Pine\Configuration
 */
class SerializerFactory
{
    /**
     * @return Serializer
     */
    public static function create()
    {
        return SerializerBuilder::create()->build();
    }
}
