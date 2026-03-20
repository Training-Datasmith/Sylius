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
namespace Sylius\Behat\Page\Admin\Payment_Method;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function name_it(string $name, string $language_code): void;
    public function enable_sandbox_mode(): void;
    public function is_code_disabled(): bool;
    public function is_factory_name_field_disabled(): bool;
    public function is_use_payum_field_disabled(): bool;
    public function is_payment_method_enabled(): bool;
    public function is_payment_method_in_sandbox_mode(): bool;
    public function is_available_in_channel(string $channel_name): bool;
    public function get_payment_method_instructions(string $language): string;
}