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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Bundle\Theme_Bundle\Model\Theme_Interface;
use Sylius\Bundle\Theme_Bundle\Repository\Theme_Repository_Interface;
final readonly class Theme_Context implements Context
{
    public function __construct(private Theme_Repository_Interface $theme_repository)
    {
    }
    #[Transform('/^"([^"]+)" theme$/')]
    #[Transform('/^theme "([^"]+)"$/')]
    #[Transform(':theme')]
    public function get_theme_by_theme_name(string $theme_name): Theme_Interface
    {
        return $this->theme_repository->find_one_by_name($theme_name);
    }
}