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
namespace Sylius\Behat\Element;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Service\Driver_Helper;
class Save_Element extends Sylius_Element implements Save_Element_Interface
{
    public function save_changes(): void
    {
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $this->get_document()->find('css', 'body')->click();
            Driver_Helper::wait_for_page_to_load($this->get_session());
        }
        try {
            $this->get_element('update_changes_button')->press();
        } catch (Element_Not_Found_Exception) {
            // Fallback for elements with different data-test attributes
            $this->get_element('save_changes_button')->press();
        }
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['save_changes_button' => '[data-test-button="save-changes"]', 'update_changes_button' => '[data-test-update-changes-button]']);
    }
}