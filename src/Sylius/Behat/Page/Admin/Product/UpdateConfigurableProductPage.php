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
namespace Sylius\Behat\Page\Admin\Product;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Context\Ui\Admin\Helper\Navigation_Trait;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
use Sylius\Behat\Service\Autocomplete_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Update_Configurable_Product_Page extends Base_Update_Page implements Update_Configurable_Product_Page_Interface
{
    use Checks_Code_Immutability;
    use Navigation_Trait;
    /**
     * @param array<array-key, string> $minkParameters
     */
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $route_name);
    }
    protected function get_resource_name(): string
    {
        return 'product';
    }
    public function save_changes(): void
    {
        $this->wait_for_form_update();
        parent::save_changes();
    }
    public function is_product_option_chosen(string $option): bool
    {
        $option_element = $this->get_element('options')->get_parent();
        return Autocomplete_Helper::is_value_visible($this->get_session(), $option_element, $option);
    }
    public function is_product_options_disabled(): bool
    {
        return 'disabled' === $this->get_element('options')->get_attribute('disabled');
    }
    public function has_tab(string $name): bool
    {
        return $this->has_element('side_navigation_tab', ['%name%' => $name]);
    }
    public function check_channel(string $channel_code): void
    {
        $this->get_element('channel', ['%channel_code%' => $channel_code])->check();
    }
    public function go_to_variants_list(): void
    {
        $this->get_document()->click_link('List variants');
    }
    public function go_to_variant_creation(): void
    {
        $this->get_document()->click_link('Create');
    }
    public function go_to_variant_generation(): void
    {
        $this->get_document()->click_link('Generate');
    }
    public function specify_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function specify_field(string $field, string $value): void
    {
        $this->get_element(lcfirst($field))->set_value($value);
    }
    public function select_option(string $option_name): void
    {
        $this->change_tab('details');
        $product_options_autocomplete = $this->get_element('product_options_autocomplete');
        $this->autocomplete_helper->select_by_name($this->get_driver(), $product_options_autocomplete->get_xpath(), $option_name);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel' => '[data-test-channel-code="%channel_code%"]', 'channel_tab' => '[data-test-channel-tab^="%channelCode%_"]', 'channels' => '[data-test-channels]', 'code' => '[data-test-code]', 'enabled' => '[data-test-enabled]', 'options' => '[data-test-options]', 'product_options_autocomplete' => '[data-test-product-options-autocomplete]', 'show_product_button' => '[data-test-show-product]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function get_element(string $name, array $parameters = []): Node_Element
    {
        if (!isset($parameters['%locale%'])) {
            $parameters['%locale%'] = 'en_US';
        }
        return parent::get_element($name, $parameters);
    }
}