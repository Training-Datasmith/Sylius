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
namespace Sylius\Behat\Page\Shop\Contact;

use Sylius\Behat\Page\Shop\Page as ShopPage;
class Contact_Page extends Shop_Page implements Contact_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_shop_contact_request';
    }
    public function send(): void
    {
        $this->get_element('send_button')->click();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['email' => '[data-test-contact-email]', 'message' => '[data-test-contact-message]', 'send_button' => '[data-test-button="contact-send"]']);
    }
}