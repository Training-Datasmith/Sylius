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
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Checks_Code_Immutability;
    use Toggles;
    public function name_it(string $name, string $language_code): void
    {
        $this->get_document()->fill_field(sprintf('sylius_admin_payment_method_translations_%s_name', $language_code), $name);
    }
    public function enable_sandbox_mode(): void
    {
        $this->get_element('sandbox')->check();
    }
    public function is_payment_method_enabled(): bool
    {
        return (bool) $this->get_toggleable_element()->get_value();
    }
    public function is_payment_method_in_sandbox_mode(): bool
    {
        return $this->get_element('sandbox')->has_attribute('checked');
    }
    public function is_factory_name_field_disabled(): bool
    {
        return 'disabled' === $this->get_element('factory_name')->get_attribute('disabled');
    }
    public function is_use_payum_field_disabled(): bool
    {
        return 'disabled' === $this->get_element('use_payum')->get_attribute('disabled');
    }
    public function is_available_in_channel(string $channel_name): bool
    {
        return $this->get_element('channel', ['%channel_name%' => $channel_name])->has_attribute('checked');
    }
    public function get_payment_method_instructions(string $language): string
    {
        return $this->get_element('instructions', ['%language%' => $language])->get_text();
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
        return array_merge(parent::get_defined_elements(), ['channel' => '[data-test-channel-name="%channel_name%"]', 'code' => '[data-test-code]', 'enabled' => '[data-test-enabled]', 'factory_name' => '[data-test-factory-name]', 'instructions' => '#sylius_admin_payment_method_translations_%language%_instructions', 'name' => '#sylius_admin_payment_method_translations_en_US_name', 'password' => '[data-test-password]', 'publishable_key' => '[data-test-publishable-key]', 'sandbox' => '[data-test-sandbox]', 'secret_key' => '[data-test-secret-key]', 'signature' => '[data-test-signature]', 'use_payum' => '[data-test-use-payum]', 'username' => '[data-test-username]']);
    }
}