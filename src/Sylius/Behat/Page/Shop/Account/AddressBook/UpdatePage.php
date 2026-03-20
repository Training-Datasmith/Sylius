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
namespace Sylius\Behat\Page\Shop\Account\Address_Book;

use Behat\Mink\Exception\Driver_Exception;
use Friends_Of_Behat\Page_Object_Extension\Page\Unexpected_Page_Exception;
use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
class Update_Page extends Sylius_Page implements Update_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_account_address_book_update';
    }
    public function fill_field(string $field, ?string $value): void
    {
        $field = $this->get_element(str_replace(' ', '_', strtolower($field)));
        $field->set_value($value);
    }
    public function get_specified_province(): string
    {
        $this->wait_for_element(5, 'province_name');
        return $this->get_element('province_name')->get_value();
    }
    public function get_selected_province(): string
    {
        $this->wait_for_element(5, 'province_code');
        return $this->get_element('selected_province')->get_text();
    }
    public function specify_province(string $name): void
    {
        $this->wait_for_element(5, 'province_name');
        $province = $this->get_element('province_name');
        $province->set_value($name);
    }
    public function select_province(string $name): void
    {
        $this->wait_for_element(5, 'province_code');
        $province = $this->get_element('province_code');
        $province->select_option($name);
    }
    public function select_country(string $name): void
    {
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
        $country = $this->get_element('country');
        $country->select_option($name);
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
    }
    public function wait_for_form_to_stop_loading(): void
    {
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
    }
    public function save_changes(): void
    {
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
        $this->get_element('save_button')->press();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['city' => '[data-test-city]', 'country' => '[data-test-country]', 'first_name' => '[data-test-first-name]', 'last_name' => '[data-test-last-name]', 'postcode' => '[data-test-postcode]', 'province_name' => '[data-test-province-name]', 'province_code' => '[data-test-province-code]', 'save_button' => '[data-test-button="save-changes"]', 'selected_province' => '[data-test-province-code] option[selected="selected"]', 'street' => '[data-test-street]']);
    }
    protected function verify_status_code(): void
    {
        try {
            $status_code = $this->get_session()->get_status_code();
        } catch (Driver_Exception) {
            return;
            // Ignore drivers which cannot check the response status code
        }
        if ($status_code >= 200 && $status_code <= 299 || $status_code === 422) {
            return;
        }
        $current_url = $this->get_session()->get_current_url();
        $message = sprintf('Could not open the page: "%s". Received an error status code: %s', $current_url, $status_code);
        throw new Unexpected_Page_Exception($message);
    }
    protected function wait_for_element(int $timeout, string $element_name): void
    {
        $this->get_document()->wait_for($timeout, fn() => $this->has_element($element_name));
    }
}