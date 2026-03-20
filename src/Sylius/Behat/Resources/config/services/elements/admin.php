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
use Sylius\Behat\Element\Admin\Account\Reset_Element;
use Sylius\Behat\Element\Admin\Catalog_Promotion\Filter_Element as CatalogPromotionFilterElement;
use Sylius\Behat\Element\Admin\Catalog_Promotion\Form_Element as CatalogPromotionFormElement;
use Sylius\Behat\Element\Admin\Channel\Discounted_Products_Checking_Period_Input_Element;
use Sylius\Behat\Element\Admin\Channel\Discounted_Products_Checking_Period_Input_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Exclude_Taxons_From_Showing_Lowest_Price_Input_Element;
use Sylius\Behat\Element\Admin\Channel\Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Lowest_Price_Flag_Element;
use Sylius\Behat\Element\Admin\Channel\Lowest_Price_Flag_Element_Interface;
use Sylius\Behat\Element\Admin\Channel\Shipping_Address_In_Checkout_Required_Element;
use Sylius\Behat\Element\Admin\Channel\Shop_Billing_Data_Element;
use Sylius\Behat\Element\Admin\Crud\Form_Element;
use Sylius\Behat\Element\Admin\Crud\Index\Search_Filter_Element;
use Sylius\Behat\Element\Admin\Currency\Form_Element as CurrencyFormElement;
use Sylius\Behat\Element\Admin\Customer\Form_Element as CustomerFormElement;
use Sylius\Behat\Element\Admin\Customer_Group\Form_Element as CustomerGroupFormElement;
use Sylius\Behat\Element\Admin\Exchange_Rate\Form_Element as ExchangeRateFormElement;
use Sylius\Behat\Element\Admin\Locale\Form_Element as LocaleFormElement;
use Sylius\Behat\Element\Admin\Notifications_Element;
use Sylius\Behat\Element\Admin\Product_Option\Form_Element as ProductOptionFormElement;
use Sylius\Behat\Element\Admin\Promotion\Form_Element as PromotionFormElement;
use Sylius\Behat\Element\Admin\Promotion\Form_Element_Interface;
use Sylius\Behat\Element\Admin\Promotion_Coupon\Form_Element as PromotionCouponFormElement;
use Sylius\Behat\Element\Admin\Shipping_Method\Form_Element as ShippingMethodFormElement;
use Sylius\Behat\Element\Admin\Tax_Category\Form_Element as TaxCategoryFormElement;
use Sylius\Behat\Element\Admin\Taxon\Form_Element as TaxonFormElement;
use Sylius\Behat\Element\Admin\Taxon\Image_Form_Element;
use Sylius\Behat\Element\Admin\Taxon\Tree_Element;
use Sylius\Behat\Element\Admin\Tax_Rate\Filter_Element as TaxRateFilterElement;
use Sylius\Behat\Element\Admin\Top_Bar_Element;
use Sylius\Behat\Element\Admin\Zone\Form_Element as ZoneFormElement;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->set('sylius.behat.element.admin.crud.form', Form_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.crud.index.search_filter', Search_Filter_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.account.reset', Reset_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.channel.shipping_address_in_checkout_required', Shipping_Address_In_Checkout_Required_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.channel.shop_billing_data', Shop_Billing_Data_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.notifications', Notifications_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.top_bar', Top_Bar_Element::class)->parent('sylius.behat.element');
    $services->set(Catalog_Promotion_Form_Element::class)->parent('sylius.behat.element')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set(Catalog_Promotion_Filter_Element::class)->parent('sylius.behat.element');
    $services->set(Tax_Rate_Filter_Element::class)->parent('sylius.behat.element');
    $services->set(Discounted_Products_Checking_Period_Input_Element_Interface::class, Discounted_Products_Checking_Period_Input_Element::class)->parent('sylius.behat.element');
    $services->set(Lowest_Price_Flag_Element_Interface::class, Lowest_Price_Flag_Element::class)->parent('sylius.behat.element');
    $services->set(Exclude_Taxons_From_Showing_Lowest_Price_Input_Element_Interface::class, Exclude_Taxons_From_Showing_Lowest_Price_Input_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set(Form_Element_Interface::class, Promotion_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.shipping_method.form', Shipping_Method_Form_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.product_option.form', Product_Option_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.customer.form', Customer_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.element.admin.zone.form', Zone_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.taxon.form', Taxon_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.taxon.image_form', Image_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.taxon.tree', Tree_Element::class)->parent('sylius.behat.element');
    $services->set('sylius.behat.element.admin.promotion_coupon.form', Promotion_Coupon_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.tax_category.form', Tax_Category_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.currency.form', Currency_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.locale.form', Locale_Form_Element::class)->parent('sylius.behat.element.admin.crud.form')->args([service(Autocomplete_Helper_Interface::class)]);
    $services->set('sylius.behat.element.admin.exchange_rate.form', Exchange_Rate_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
    $services->set('sylius.behat.element.admin.customer_group.form', Customer_Group_Form_Element::class)->parent('sylius.behat.element.admin.crud.form');
};