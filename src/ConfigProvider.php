<?php

namespace Bermuda\Authentication;

/**
 * Class ConfigProvider
 * @package Bermuda\MiddlewareFactory
 */
class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    protected function getFactories(): array
    {
        return [AdapterInterface::class => AdapterFactory::class];
    }
}
