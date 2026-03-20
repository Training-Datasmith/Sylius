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
use Sylius\Component\Core\Model\Address_Interface;
class Create_Page extends Sylius_Page implements Create_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_account_address_book_create';
    }
    public function fill_address_data(Address_Interface $address): void
    {
        $this->get_element('first_name')->set_value($address->get_first_name());
        $this->get_element('last_name')->set_value($address->get_last_name());
        $this->get_element('street')->set_value($address->get_street());
        $this->get_element('country')->select_option($address->get_country_code());
        $this->get_element('city')->set_value($address->get_city());
        $this->get_element('postcode')->set_value($address->get_postcode());
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
    }
    public function select_country(string $name): void
    {
        $this->get_element('country')->select_option($name);
        Driver_Helper::wait_for_form_to_stop_loading($this->get_session());
    }
    public function add_address(): void
    {
        $this->get_element('add_button')->press();
    }
    public function has_province_validation_message(): bool
    {
        return $this->has_element('province_validation_message');
    }
    public function count_validation_messages(): int
    {
        return count($this->get_document()->find_all('css', '[data-test-validation-error]'));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_button' => '[data-test-button="add-address"]', 'city' => '[data-test-city]', 'country' => '[data-test-country]', 'first_name' => '[data-test-first-name]', 'last_name' => '[data-test-last-name]', 'postcode' => '[data-test-postcode]', 'street' => '[data-test-street]', 'province_validation_message' => '[data-test-validation-error]:contains("province")']);
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
}