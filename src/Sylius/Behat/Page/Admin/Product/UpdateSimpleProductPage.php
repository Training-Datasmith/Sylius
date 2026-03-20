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
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Symfony\Component\Routing\Router_Interface;
class Update_Simple_Product_Page extends Base_Update_Page implements Update_Simple_Product_Page_Interface
{
    use Checks_Code_Immutability;
    use Navigation_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
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
    public function disable_tracking(): void
    {
        $this->get_element('tracked')->uncheck();
    }
    public function enable_tracking(): void
    {
        $this->get_element('tracked')->check();
    }
    public function is_tracked(): bool
    {
        return $this->get_element('tracked')->is_checked();
    }
    public function go_to_variants_list(): void
    {
        $this->get_document()->click_link('List variants');
    }
    public function go_to_variant_creation(): void
    {
        $this->get_document()->click_link('Create');
    }
    public function has_generate_variants_button(): bool
    {
        return $this->has_element('generate_variants_button');
    }
    public function go_to_variant_generation(): void
    {
        $this->get_element('generate_variants_button')->click();
    }
    public function get_show_product_in_single_channel_url(): string
    {
        return $this->get_element('view_in_store')->get_attribute('href');
    }
    public function is_show_in_shop_button_disabled(): bool
    {
        return $this->get_element('view_in_store')->has_class('disabled');
    }
    public function show_product_in_channel(Channel_Interface $channel): void
    {
        $this->get_element('view_in_store_in_channel', ['%channel_code%' => $channel->get_code()])->click();
    }
    public function show_product_in_single_channel(): void
    {
        $this->get_element('view_in_store')->click();
    }
    public function disable(): void
    {
        $this->get_element('enabled')->uncheck();
    }
    public function is_enabled(): bool
    {
        return $this->get_element('enabled')->is_checked();
    }
    public function enable(): void
    {
        $this->get_element('enabled')->check();
    }
    public function specify_code(string $code): void
    {
        $this->get_element('code')->set_value($code);
    }
    public function specify_field(string $field, string $value): void
    {
        $this->get_element($field)->set_value($value);
    }
    public function is_shipping_required(): bool
    {
        return $this->get_element('field_shipping_required')->is_checked();
    }
    public function has_tab(string $name): bool
    {
        return $this->has_element('side_navigation_tab', ['%name%' => $name]);
    }
    protected function get_element(string $name, array $parameters = []): Node_Element
    {
        if (!isset($parameters['%locale%'])) {
            $parameters['%locale%'] = 'en_US';
        }
        return parent::get_element($name, $parameters);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    /** @return array<string, string> */
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'create_variant_button' => '[data-test-create]', 'enabled' => '[data-test-enabled]', 'field_shipping_category' => '[name="sylius_admin_product[variant][shippingCategory]"]', 'field_shipping_required' => '[name="sylius_admin_product[variant][shippingRequired]"]', 'generate_variants_button' => '[data-test-generate]', 'list_variants_button' => '[data-test-list]', 'product_translation_accordion' => '[data-test-product-translations-accordion="%localeCode%"]', 'show_product_button' => '[data-test-show-product]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]', 'tracked' => '[name="sylius_admin_product[variant][tracked]"]', 'view_in_store' => '[data-test-view-in-store]', 'view_in_store_in_channel' => '[data-test-view-in-store] [data-test-channel-code="%channel_code%"]']);
    }
}