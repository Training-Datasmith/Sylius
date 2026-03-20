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
namespace Sylius\Behat\Element\Admin\Taxon;

use Sylius\Behat\Element\Sylius_Element;
use Sylius\Behat\Service\Driver_Helper;
class Tree_Element extends Sylius_Element implements Tree_Element_Interface
{
    public function get_taxons_names(): array
    {
        $tree_taxons = $this->get_element('tree_taxons');
        $taxons = [];
        foreach ($tree_taxons->find_all('css', '[data-test-tree-taxon]') as $taxon) {
            $taxons[] = $taxon->get_text();
        }
        return $taxons;
    }
    public function count_taxons(): int
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        return count($this->get_element('tree_taxons')->find_all('css', '[data-test-tree-taxon]'));
    }
    public function is_taxon_on_the_list(string $taxon_name): bool
    {
        $taxons = $this->get_element('tree_taxons')->find_all('css', '[data-test-tree-taxon]');
        foreach ($taxons as $taxon) {
            if ($taxon_name === $taxon->get_text()) {
                return true;
            }
        }
        return false;
    }
    public function get_first_taxon_on_the_list(): string
    {
        return $this->get_element('first_tree_taxon')->get_text();
    }
    public function get_last_taxon_on_the_list(): string
    {
        return $this->get_element('last_tree_taxon')->get_text();
    }
    public function move_up_taxon(string $name): void
    {
        $this->get_element('tree_taxon_actions', ['%name%' => $name])->click();
        $this->get_element('tree_taxon_move_up', ['%name%' => $name])->click();
        $this->wait_for_update();
    }
    public function move_down_taxon(string $name): void
    {
        $this->get_element('tree_taxon_actions', ['%name%' => $name])->click();
        $this->get_element('tree_taxon_move_down', ['%name%' => $name])->click();
        $this->wait_for_update();
    }
    public function delete_taxon(string $name): void
    {
        $this->get_element('tree_taxon_actions', ['%name%' => $name])->click();
        $this->get_element('tree_taxon_delete', ['%name%' => $name])->click();
        $this->wait_for_update('tree_taxon_delete_component');
        $this->get_element('confirm_delete_button', ['%name%' => $name])->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['confirm_delete_button' => '[data-test-tree-taxons] [data-test-tree-taxon="%name%"] [data-test-delete-modal] [data-test-confirm-button]', 'first_tree_taxon' => '[data-test-tree-taxons] [data-test-tree-taxon]:first-child', 'last_tree_taxon' => '[data-test-tree-taxons] [data-test-tree-taxon]:last-child', 'tree_taxons' => '[data-test-tree-taxons]', 'tree_taxon_actions' => '[data-test-tree-taxons] [data-test-tree-taxon="%name%"] [data-test-actions]', 'tree_taxon_component' => '[data-live-name-value="sylius_admin:taxon:tree"]', 'tree_taxon_delete' => '[data-test-tree-taxons] [data-test-tree-taxon="%name%"] [data-test-delete]', 'tree_taxon_delete_component' => '[data-live-name-value="sylius_admin:taxon:delete"]', 'tree_taxon_move_down' => '[data-test-tree-taxons] [data-test-tree-taxon="%name%"] [data-test-move-down]', 'tree_taxon_move_up' => '[data-test-tree-taxons] [data-test-tree-taxon="%name%"] [data-test-move-up]']);
    }
    protected function wait_for_update(string $element = 'tree_taxon_component'): void
    {
        $element_component = $this->get_element($element);
        sleep(1);
        // we need to sleep, as sometimes the check below is executed faster than the treeTaxonComponent sets the busy attribute
        $element_component->wait_for(1500, fn() => !$element_component->has_attribute('busy'));
    }
}