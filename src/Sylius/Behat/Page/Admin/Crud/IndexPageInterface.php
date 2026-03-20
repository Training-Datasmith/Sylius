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
namespace Sylius\Behat\Page\Admin\Crud;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Sylius_Page_Interface;
interface Index_Page_Interface extends Sylius_Page_Interface
{
    public function is_single_resource_on_page(array $parameters): bool;
    public function is_single_resource_with_specific_element_on_page(array $parameters, string $element): bool;
    public function get_column_fields(string $column_name): array;
    public function sort_by(string $field_name, ?string $order = null): void;
    public function get_cell_for_resource(string $header, array $parameters): Node_Element;
    public function delete_resource_on_page(array $parameters): void;
    public function get_actions_for_resource(array $parameters): Node_Element;
    public function check_resource_on_page(array $parameters): void;
    public function count_items(): int;
    public function choose_enabled_filter(): void;
    public function filter(): void;
    public function bulk_delete(): void;
    public function sort(string $order): void;
    public function is_enabled_filter_applied(): bool;
}