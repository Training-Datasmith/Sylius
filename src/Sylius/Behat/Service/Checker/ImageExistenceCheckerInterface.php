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
namespace Sylius\Behat\Service\Checker;

interface Image_Existence_Checker_Interface
{
    public function does_image_with_url_exist(string $image_url, string $liip_imagine_imagine_filter): bool;
}