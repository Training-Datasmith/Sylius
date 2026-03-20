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
namespace Sylius\Behat\Element\Admin\Promotion_Coupon;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Specifies_Its_Field;
    use Checks_Code_Immutability;
    public function set_usage_limit(int $limit): void
    {
        $this->get_element('usage_limit')->set_value($limit);
    }
    public function set_customer_usage_limit(int $limit): void
    {
        $this->get_element('per_customer_usage_limit')->set_value($limit);
    }
    public function set_expires_at(\DateTimeInterface $date): void
    {
        $this->get_element('expires_at')->set_value($date->format('Y-m-d'));
    }
    public function toggle_reusable_from_cancelled_orders(bool $reusable): void
    {
        $this->get_element('reusable_from_cancelled_orders')->set_value($reusable);
    }
    public function is_reusable_from_cancelled_orders(): bool
    {
        return $this->get_element('reusable_from_cancelled_orders')->is_checked();
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'expires_at' => '[data-test-expires-at]', 'per_customer_usage_limit' => '[data-test-per-customer-usage-limit]', 'reusable_from_cancelled_orders' => '[data-test-reusable-from-cancelled-orders]', 'usage_limit' => '[data-test-usage-limit]']);
    }
}