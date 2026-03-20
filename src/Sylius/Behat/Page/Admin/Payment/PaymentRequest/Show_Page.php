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
namespace Sylius\Behat\Page\Admin\Payment\Payment_Request;

use Sylius\Behat\Page\Sylius_Page;
class Show_Page extends Sylius_Page implements Show_Page_Interface
{
    public function get_route_name(): string
    {
        return 'sylius_admin_payment_request_show';
    }
    public function get_field_text(string $field_name): string
    {
        return $this->get_element($field_name)->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['action' => '[data-test-action]', 'method' => '[data-test-method]', 'payload' => '[data-test-payload]', 'response_data' => '[data-test-response-data]', 'state' => '[data-test-state]']);
    }
}