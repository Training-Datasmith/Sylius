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
namespace Sylius\Behat\Page;

use Friends_Of_Behat\Page_Object_Extension\Page\Page;
class Error_Page extends Page implements Error_Page_Interface
{
    protected function get_url(array $url_parameters = []): string
    {
        // This page does not have any url
        return '';
    }
    public function get_code(): int
    {
        return $this->get_session()->get_status_code();
    }
    public function is_it_admin_not_found_page(): bool
    {
        return $this->get_code() === 404 && $this->has_element('admin_back_to_dashboard_link');
    }
    public function is_it_shop_not_found_page(): bool
    {
        return $this->get_code() === 404 && $this->has_element('shop_not_found_page');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['admin_back_to_dashboard_link' => '[data-test-back-to-dashboard-link]', 'shop_not_found_page' => '[data-test-shop-not-found-page]']);
    }
}