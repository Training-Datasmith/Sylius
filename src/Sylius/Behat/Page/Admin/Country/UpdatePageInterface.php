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
namespace Sylius\Behat\Page\Admin\Country;

use Sylius\Behat\Page\Admin\Crud\Update_Page_Interface as BaseUpdatePageInterface;
interface Update_Page_Interface extends Base_Update_Page_Interface
{
    public function enable(): void;
    public function disable(): void;
    public function is_code_field_disabled(): bool;
    public function add_province(): void;
    public function specify_province_name(string $name): void;
    public function specify_province_code(string $code): void;
    public function specify_province_abbreviation(string $abbreviation): void;
    public function is_there_province(string $province_name): bool;
    public function is_there_province_with_code(string $province_code): bool;
    public function remove_province(string $province_name): void;
    /** @return array<array-key, string> */
    public function get_form_validation_errors(): array;
}