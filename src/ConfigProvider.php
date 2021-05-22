<?php

namespace Bermuda\Authentication;

/**
 * Class ConfigProvider
 * @package Bermuda\MiddlewareFactory
 */
final class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    protected function getFactories(): array
    {
        return [AdapterInterface::class => AdapterFactory::class];
    }
}
