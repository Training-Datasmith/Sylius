<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
use Symfony\Component\Config\Loader\Loader_Interface;
use Symfony\Component\Http_Kernel\Kernel;
class App_Kernel extends Kernel
{
    public function register_bundles(): iterable
    {
        return [new Symfony\Bundle\Framework_Bundle\Framework_Bundle(), new Bab_Dev\Pagerfanta_Bundle\Bab_Dev_Pagerfanta_Bundle(), new Doctrine\Bundle\Doctrine_Bundle\Doctrine_Bundle(), new Sylius\Bundle\Addressing_Bundle\Sylius_Addressing_Bundle(), new Sylius\Bundle\Resource_Bundle\Sylius_Resource_Bundle(), new Symfony\Bundle\Twig_Bundle\Twig_Bundle(), new Nelmio\Alice\Bridge\Symfony\Nelmio_Alice_Bundle(), new Fidry\Alice_Data_Fixtures\Bridge\Symfony\Fidry_Alice_Data_Fixtures_Bundle()];
    }
    public function register_container_configuration(Loader_Interface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }
}