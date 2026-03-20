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

use Sylius\Behat\Page\Sylius_Page;
use Sylius\Behat\Service\Driver_Helper;
use Webmozart\Assert\Assert;
class Index_Page extends Sylius_Page implements Index_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_account_address_book_index';
    }
    public function get_addresses_count(): int
    {
        $addresses_count = count($this->get_element('addresses')->find_all('css', '[data-test-address]'));
        if (!$this->has_no_default_address()) {
            ++$addresses_count;
        }
        return $addresses_count;
    }
    public function has_address_of(string $full_name): bool
    {
        return $this->has_element('address', ['%full_name%' => $full_name]);
    }
    public function has_no_addresses(): bool
    {
        return $this->has_element('content', ['%message%' => 'You have no addresses defined']);
    }
    public function address_of_contains(string $full_name, string $value): bool
    {
        return $this->has_element('address', ['%full_name%' => $full_name, '%value%' => $value]);
    }
    public function edit_address(string $full_name): void
    {
        $this->get_element('edit_address', ['%full_name%' => $full_name])->press();
    }
    public function delete_address(string $full_name): void
    {
        $this->get_element('delete_button', ['%full_name%' => $full_name])->press();
        Driver_Helper::wait_for_page_to_load($this->get_session());
    }
    public function set_as_default(string $full_name): void
    {
        $this->get_element('set_as_default_button', ['%full_name%' => $full_name])->press();
    }
    public function has_no_default_address(): bool
    {
        return !$this->has_element('default_address');
    }
    public function get_full_name_of_default_address(): string
    {
        $full_name_element = $this->get_element('default_address');
        Assert::not_null($full_name_element, 'There should be a default address\'s full name.');
        return $full_name_element->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['address' => '[data-test-address="%full_name%"]', 'address_contains' => '[data-test-address-context="%full_name%"]:contains("%value%")', 'addresses' => '[data-test-addresses]', 'content' => '[data-test-sylius-flash-message="alert-info"]:contains("%message%")', 'default_address' => '[data-test-default-address] [data-test-full-name]', 'delete_button' => '[data-test-address="%full_name%"] [data-test-button="delete"]', 'edit_address' => '[data-test-address="%full_name%"] [data-test-edit-button]', 'set_as_default_button' => '[data-test-address="%full_name%"] [data-test-button="set-as-default-button"]']);
    }
}