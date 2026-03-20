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
use Sylius\Behat\Context\Ui\Admin\Helper\Navigation_Trait;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Create_Simple_Product_Page extends Base_Create_Page implements Create_Simple_Product_Page_Interface
{
    use Navigation_Trait;
    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param array<TKey, TValue>|\ArrayAccess<TKey, TValue> $minkParameters
     */
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $route_name);
    }
    public function get_resource_name(): string
    {
        return 'product';
    }
    public function get_route_name(): string
    {
        return parent::get_route_name() . '_simple';
    }
    public function create(): void
    {
        $this->wait_for_form_update();
        parent::create();
    }
    public function check_channel(string $channel_code): void
    {
        $this->change_tab('details');
        $this->get_element('channel', ['%channel_code%' => $channel_code])->check();
    }
    protected function change_tab(string $tab_name): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => $tab_name])->click();
    }
    public function specify_code(string $code): void
    {
        $this->change_tab('details');
        $this->get_element('code')->set_value($code);
    }
    public function specify_field(string $field, string $value): void
    {
        $this->get_element($field)->set_value($value);
    }
    public function select_shipping_category(string $shipping_category_name): void
    {
        $this->change_tab('shipping');
        $this->get_element('field_shipping_category')->select_option($shipping_category_name);
    }
    public function set_shipping_required(bool $is_shipping_required): void
    {
        $this->change_tab('details');
        if ($is_shipping_required) {
            $this->get_element('field_shipping_required')->check();
            return;
        }
        $this->get_element('field_shipping_required')->uncheck();
    }
    public function is_shipping_required(): bool
    {
        return $this->get_element('field_shipping_required')->is_checked();
    }
    protected function get_element(string $name, array $parameters = []): Node_Element
    {
        if (!isset($parameters['%locale%'])) {
            $parameters['%locale%'] = 'en_US';
        }
        return parent::get_element($name, $parameters);
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['channel' => '[data-test-channel-code="%channel_code%"]', 'code' => '[data-test-code]', 'enabled' => '[data-test-enabled]', 'field_shipping_category' => '[name="sylius_admin_product[variant][shippingCategory]"]', 'field_shipping_required' => '[name="sylius_admin_product[variant][shippingRequired]"]', 'product_translation_accordion' => '[data-test-product-translations-accordion="%localeCode%"]', 'show_product_button' => '[data-test-show-product]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
}