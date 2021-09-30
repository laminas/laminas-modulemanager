<?php

namespace Laminas\ModuleManager\Feature;

interface SerializerProviderInterface
{
    /**
     * @return array
     */
    public function getSerializerConfig();
}
