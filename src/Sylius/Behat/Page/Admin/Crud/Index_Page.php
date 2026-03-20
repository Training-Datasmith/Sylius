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
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Accessor\Table_Accessor_Interface;
use Sylius\Behat\Service\Driver_Helper;
use Symfony\Component\Routing\Router_Interface;
use Webmozart\Assert\Assert;
class Index_Page extends Sylius_Page implements Index_Page_Interface
{
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, protected readonly Table_Accessor_Interface $table_accessor, protected readonly string $route_name)
    {
    }
    public function is_single_resource_on_page(array $parameters): bool
    {
        try {
            $rows = $this->table_accessor->get_rows_with_fields($this->get_element('table'), $parameters);
            return 1 === count($rows);
        } catch (Element_Not_Found_Exception|\InvalidArgumentException) {
            return false;
        }
    }
    public function get_column_fields(string $column_name): array
    {
        return $this->table_accessor->get_indexed_column($this->get_element('table'), $column_name);
    }
    public function sort_by(string $field_name, ?string $order = null): void
    {
        $sortable_headers = $this->table_accessor->get_sortable_headers($this->get_element('table'));
        Assert::key_exists($sortable_headers, $field_name, sprintf('Column "%s" does not exist or is not sortable.', $field_name));
        /** @var NodeElement $sortingHeader */
        $sorting_header = $sortable_headers[$field_name]->find('css', 'a');
        preg_match('/\?sorting[^=]+\=([acdes]+)/i', (string) $sorting_header->get_attribute('href'), $matches);
        $next_sorting_order = $matches[1] ?? 'desc';
        $sortable_headers[$field_name]->find('css', 'a')->click();
        if (null !== $order && $order !== $next_sorting_order) {
            $sortable_headers[$field_name]->find('css', 'a')->click();
        }
    }
    public function is_single_resource_with_specific_element_on_page(array $parameters, string $element): bool
    {
        try {
            $rows = $this->table_accessor->get_rows_with_fields($this->get_element('table'), $parameters);
            if (1 !== count($rows)) {
                return false;
            }
            return null !== $rows[0]->find('css', $element);
        } catch (Element_Not_Found_Exception|\InvalidArgumentException) {
            return false;
        }
    }
    public function count_items(): int
    {
        try {
            return $this->get_table_accessor()->count_table_body_rows($this->get_element('table'));
        } catch (Element_Not_Found_Exception) {
            return 0;
        }
    }
    public function get_cell_for_resource(string $header, array $parameters): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $resource_row = $table_accessor->get_row_with_fields($table, $parameters);
        return $table_accessor->get_field_from_row($table, $resource_row, $header);
    }
    public function delete_resource_on_page(array $parameters): void
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $deleted_row = $table_accessor->get_row_with_fields($table, $parameters);
        $action_buttons = $table_accessor->get_field_from_row($table, $deleted_row, 'actions');
        $action_buttons->find('css', '[data-test-modal="delete"] [data-test-confirm-button]')->press();
    }
    public function get_actions_for_resource(array $parameters): Node_Element
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $resource_row = $table_accessor->get_row_with_fields($table, $parameters);
        return $table_accessor->get_field_from_row($table, $resource_row, 'actions');
    }
    public function check_resource_on_page(array $parameters): void
    {
        $table_accessor = $this->get_table_accessor();
        $table = $this->get_element('table');
        $resource_row = $table_accessor->get_row_with_fields($table, $parameters);
        $bulk_checkbox = $resource_row->find('css', '.form-check-input');
        Assert::not_null($bulk_checkbox);
        $bulk_checkbox->check();
    }
    public function filter(): void
    {
        $this->get_element('filter')->press();
    }
    public function bulk_delete(): void
    {
        $this->get_element('bulk_actions')->press_button('Delete');
        $this->get_element('bulk_delete_confirm_button')->click();
    }
    public function sort(string $order): void
    {
        $this->get_document()->click_link($order);
    }
    public function choose_enabled_filter(): void
    {
        $this->get_element('enabled_filter')->select_option('Yes');
    }
    public function is_enabled_filter_applied(): bool
    {
        Driver_Helper::wait_for_element($this->get_session(), '[data-test-criterion-enabled]');
        return $this->get_element('enabled_filter')->get_value() === 'true';
    }
    public function get_route_name(): string
    {
        return $this->route_name;
    }
    public function go_to_page(int $page): void
    {
        $this->get_element('page_button', ['%page%' => $page])->click();
    }
    public function get_page_number(): int
    {
        return (int) $this->get_element('current_page')->get_text();
    }
    protected function get_table_accessor(): Table_Accessor_Interface
    {
        return $this->table_accessor;
    }
    protected function toggle_filters(): void
    {
        $filters_toggle = $this->get_element('filters_toggle');
        $filters_toggle->click();
        $this->get_document()->wait_for(1, function () use ($filters_toggle): bool {
            $accordion_collapse = $filters_toggle->find('css', '.accordion-collapse');
            return null !== $accordion_collapse && !$accordion_collapse->has_class('collapsing');
        });
    }
    protected function are_filters_visible(): bool
    {
        return !$this->get_element('filters_toggle')->has_class('collapsed');
    }
    protected function wait_for_form_update(): void
    {
        $form = $this->get_element('filters_form');
        usleep(500000);
        // we need to sleep, as sometimes the check below is executed faster than the form sets the busy attribute
        $form->wait_for(1500, fn() => !$form->has_attribute('busy'));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['bulk_actions' => '.sylius-grid-nav__bulk', 'bulk_delete_confirm_button' => '[data-test-modal="bulk-delete"] [data-test-confirm-button]', 'current_page' => '[data-test-current-page]', 'enabled_filter' => '[data-test-criterion-enabled]', 'filter' => '[data-test-filter]', 'filters_form' => '[data-test-filters-form]', 'filters_toggle' => '.accordion-button', 'page_button' => '[data-test-page="%page%"]', 'table' => '.table']);
    }
}