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

use Sylius\Behat\Page\Admin\Crud\Create_Page_Interface as BaseCreatePageInterface;
interface Create_Page_Interface extends Base_Create_Page_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function cancel_changes(): void;
    public function name_it(string $name, string $language_code): void;
    public function specify_code(string $code): void;
    public function check_channel(string $channel_name): void;
    public function describe_it(string $description, string $language_code): void;
    public function set_instructions(string $instructions, string $language_code): void;
    public function is_code_disabled(): bool;
    public function is_payment_method_enabled(): bool;
}