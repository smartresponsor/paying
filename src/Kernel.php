<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        $configured = $_SERVER['PAYMENT_TEST_CACHE_DIR'] ?? $_ENV['PAYMENT_TEST_CACHE_DIR'] ?? null;
        if ('test' === $this->environment && is_string($configured) && '' !== trim($configured)) {
            return str_replace('%kernel.project_dir%', $this->getProjectDir(), $configured);
        }

        return parent::getCacheDir();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');

        $envPackagesDir = __DIR__.'/../config/packages/'.$this->environment;
        if (is_dir($envPackagesDir)) {
            $container->import('../config/packages/'.$this->environment.'/*.yaml');
        }

        $container->import('../config/{services}.yaml');

        $envServicesFile = __DIR__.'/../config/services_'.$this->environment.'.yaml';
        if (is_file($envServicesFile)) {
            $container->import('../config/services_'.$this->environment.'.yaml');
        }

        if (is_dir(__DIR__.'/../config/services')) {
            $container->import('../config/services/*.yaml');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/*.yaml');

        $envRoutesDir = __DIR__.'/../config/routes/'.$this->environment;
        if (is_dir($envRoutesDir)) {
            $routes->import('../config/routes/'.$this->environment.'/*.yaml');
        }
    }
}
