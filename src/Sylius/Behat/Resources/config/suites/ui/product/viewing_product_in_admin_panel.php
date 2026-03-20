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
use Behat\Config\Config;
use Behat\Config\Filter\Tag_Filter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Sylius\Behat\Context\Setup\Catalog_Promotion_Context as SetupCatalogPromotionContext;
use Sylius\Behat\Context\Setup\Price_History_Context;
use Sylius\Behat\Context\Transform\Catalog_Promotion_Context as TransformCatalogPromotionContext;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_viewing_product_in_admin_panel'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.calendar', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.locale', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.product_association', 'sylius.behat.context.setup.product_attribute', 'sylius.behat.context.setup.product_option', 'sylius.behat.context.setup.product_taxon', 'sylius.behat.context.setup.shipping_category', 'sylius.behat.context.setup.taxation', 'sylius.behat.context.setup.taxonomy', Price_History_Context::class, Setup_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.currency', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.locale', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_association_type', 'sylius.behat.context.transform.product_option', 'sylius.behat.context.transform.product_option_value', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.shipping_category', 'sylius.behat.context.transform.tax_category', 'sylius.behat.context.transform.taxon', Transform_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.ui.admin.managing_product_attributes', 'sylius.behat.context.ui.admin.product_showpage', 'sylius.behat.context.ui.save', 'sylius.behat.context.ui.shop.browsing_product')->with_filter(new Tag_Filter('@viewing_product_in_admin_panel&&@ui'))));