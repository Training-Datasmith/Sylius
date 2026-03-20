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
namespace Sylius\Behat\Page\Admin\Payment_Method;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Checks_Code_Immutability;
    use Toggles;
    use Specifies_Its_Field;
    public function name_it(string $name, string $language_code): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_payment_method_translations_%s_name', $language_code), $name);
    }
    public function check_channel(string $channel_name): void
    {
        $this->get_document()->check_field($channel_name);
    }
    public function describe_it(string $description, string $language_code): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_payment_method_translations_%s_description', $language_code), $description);
    }
    public function set_instructions(string $instructions, string $language_code): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_payment_method_translations_%s_instructions', $language_code), $instructions);
    }
    public function is_payment_method_enabled(): bool
    {
        return (bool) $this->get_toggleable_element()->get_value();
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_toggleable_element(): Node_Element
    {
        return $this->get_element('enabled');
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '#sylius_admin_payment_method_code', 'enabled' => '#sylius_admin_payment_method_enabled', 'gateway_name' => '#sylius_admin_payment_method_gatewayConfig_gatewayName', 'name' => '#sylius_admin_payment_method_translations_en_US_name']);
    }
}