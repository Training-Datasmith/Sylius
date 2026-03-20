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
use Sylius\Behat\Context\Ui\Admin\Managing_Product_Taxons_Context;
use Sylius\Behat\Context\Ui\Admin\Removing_Product_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_managing_products'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.cache', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.admin_user', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.currency', 'sylius.behat.context.setup.geographical', 'sylius.behat.context.setup.locale', 'sylius.behat.context.setup.order', 'sylius.behat.context.setup.payment', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.product_association', 'sylius.behat.context.setup.product_attribute', 'sylius.behat.context.setup.product_option', 'sylius.behat.context.setup.product_review', 'sylius.behat.context.setup.product_taxon', 'sylius.behat.context.setup.shipping', 'sylius.behat.context.setup.shipping_category', 'sylius.behat.context.setup.taxonomy', 'sylius.behat.context.setup.zone')->with_contexts('sylius.behat.context.transform.address', 'sylius.behat.context.transform.admin', 'sylius.behat.context.transform.channel', 'sylius.behat.context.transform.currency', 'sylius.behat.context.transform.customer', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.locale', 'sylius.behat.context.transform.payment', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_association_type', 'sylius.behat.context.transform.product_option', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.shipping_method', 'sylius.behat.context.transform.taxon', 'sylius.behat.context.transform.zone')->with_contexts('sylius.behat.context.ui.admin.browsing_product_variants', 'sylius.behat.context.ui.admin.managing_administrator_locale', 'sylius.behat.context.ui.admin.managing_products', 'sylius.behat.context.ui.admin.notification', 'sylius.behat.context.ui.admin.search_filter', 'sylius.behat.context.ui.save', 'sylius.behat.context.ui.shop.browsing_product', Managing_Product_Taxons_Context::class, Removing_Product_Context::class)->with_filter(new Tag_Filter('@managing_products&&@ui'))));