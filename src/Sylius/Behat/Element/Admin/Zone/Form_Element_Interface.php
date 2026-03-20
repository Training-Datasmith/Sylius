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
namespace Sylius\Behat\Element\Admin\Zone;

use Sylius\Behat\Element\Admin\Crud\Form_Element_Interface as BaseFormElementInterface;
interface Form_Element_Interface extends Base_Form_Element_Interface
{
    public function get_name(): string;
    public function get_priority(): int;
    public function name_it(string $name): void;
    public function get_type(): string;
    public function is_type_field_disabled(): bool;
    public function is_code_disabled(): bool;
    public function specify_code(string $code): void;
    public function add_member(): void;
    public function prioritize_it(int $priority): void;
    public function get_scope(): string;
    public function select_scope(string $scope): void;
    public function has_member(string $member): bool;
    public function count_members(): int;
    public function remove_member(string $member): void;
    public function choose_member(string $name): void;
    public function get_form_validation_message(): string;
}