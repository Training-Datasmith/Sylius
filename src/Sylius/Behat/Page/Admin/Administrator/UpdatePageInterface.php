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
namespace Sylius\Behat\Page\Admin\Administrator;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface, Form_Aware_Interface
{
    public function remove_avatar(): void;
    public function has_avatar(string $avatar_path): bool;
    public function change_locale(string $locale_code): void;
}