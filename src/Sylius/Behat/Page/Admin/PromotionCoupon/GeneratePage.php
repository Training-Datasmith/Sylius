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
namespace Sylius\Behat\Page\Admin\Promotion_Coupon;

use Sylius\Behat\Page\Admin\Crud\Create_Page as BasePage;
class Generate_Page extends Base_Page implements Generate_Page_Interface
{
    public function generate(): void
    {
        $this->get_element('generate_button')->press();
    }
    public function specify_prefix(string $prefix): void
    {
        $this->get_element('prefix')->set_value($prefix);
    }
    public function specify_code_length(?int $code_length): void
    {
        $this->get_element('code_length')->set_value($code_length);
    }
    public function specify_suffix(string $suffix): void
    {
        $this->get_element('suffix')->set_value($suffix);
    }
    public function specify_amount(?int $amount): void
    {
        $this->get_element('amount')->set_value($amount);
    }
    public function set_expires_at(\DateTimeInterface $date): void
    {
        $this->get_element('expires_at')->set_value($date->format('Y-m-d'));
    }
    public function set_usage_limit(int $limit): void
    {
        $this->get_element('usage_limit')->set_value($limit);
    }
    public function get_form_validation_message(): string
    {
        return $this->get_element('form_validation_message')->get_text();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['amount' => '[data-test-amount]', 'code_length' => '[data-test-code-length]', 'expires_at' => '[data-test-expires-at]', 'form_validation_message' => 'form div.alert.alert-danger.d-block', 'generate_button' => '[data-test-generate-button]', 'prefix' => '[data-test-prefix]', 'suffix' => '[data-test-suffix]', 'usage_limit' => '[data-test-usage-limit]']);
    }
}