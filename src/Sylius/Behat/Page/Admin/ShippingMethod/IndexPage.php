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
namespace Sylius\Behat\Page\Admin\Shipping_Method;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Page\Admin\Crud\Index_Page as BaseIndexPage;
use Sylius\Component\Core\Model\Shipping_Method_Interface;
use Sylius\Resource\Model\Resource_Interface;
class Index_Page extends Base_Index_Page implements Index_Page_Interface
{
    public function choose_archival(string $is_archival): void
    {
        if (!$this->are_filters_visible()) {
            $this->toggle_filters();
        }
        $this->get_element('filter_archival')->select_option($is_archival);
    }
    public function is_archival_filter_enabled(): bool
    {
        $archival = $this->get_document()->find('css', 'button:contains("Restore")');
        return null !== $archival;
    }
    public function archive_shipping_method(string $name): void
    {
        $actions = $this->get_actions_for_resource(['name' => $name]);
        $archive_restore_modal = $actions->find('css', '[data-test-modal="archive-restore"]');
        $archive_restore_modal->find('css', '[data-test-trigger-button="Archive"]')->press();
        $archive_restore_modal->find('css', '[data-test-confirm-button]')->press();
    }
    public function restore_shipping_method(string $name): void
    {
        $actions = $this->get_actions_for_resource(['name' => $name]);
        $archive_restore_modal = $actions->find('css', '[data-test-modal="archive-restore"]');
        $archive_restore_modal->find('css', '[data-test-trigger-button="Restore"]')->press();
        $archive_restore_modal->find('css', '[data-test-confirm-button]')->press();
    }
    public function is_shipping_method_disabled(Shipping_Method_Interface $shipping_method): bool
    {
        $this->open();
        return null !== $this->get_row_for($shipping_method)->find('css', '[data-test-status-disabled]');
    }
    public function is_shipping_method_enabled(Shipping_Method_Interface $shipping_method): bool
    {
        $this->open();
        return null !== $this->get_row_for($shipping_method)->find('css', '[data-test-status-enabled]');
    }
    protected function get_row_for(Resource_Interface $shipping_method): Node_Element
    {
        return $this->get_element('row', ['%resourceId%' => $shipping_method->get_id()]);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['filter_archival' => '#criteria_archival', 'row' => '[data-test-row][data-test-resource-id="%resourceId%"]']);
    }
}