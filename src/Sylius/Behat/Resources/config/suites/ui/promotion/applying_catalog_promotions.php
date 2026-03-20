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
use Sylius\Behat\Context\Transform\Catalog_Promotion_Context as TransformCatalogPromotionContext;
use Sylius\Behat\Context\Ui\Admin\Managing_Catalog_Promotions_Context;
use Sylius\Behat\Context\Ui\Admin\Managing_Product_Taxons_Context;
use Sylius\Behat\Context\Ui\Admin\Managing_Product_Variants_Prices_Context;
use Sylius\Behat\Context\Ui\Admin\Product_Creation_Context;
use Sylius\Behat\Context\Ui\Admin\Product_Variants_Creation_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_applying_catalog_promotions'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.calendar', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.calendar', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.product_taxon', 'sylius.behat.context.setup.taxonomy', Setup_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_option_value', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.taxon', Transform_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.ui.save', 'sylius.behat.context.ui.shop.product', Managing_Catalog_Promotions_Context::class, Managing_Product_Taxons_Context::class, Managing_Product_Variants_Prices_Context::class, Product_Creation_Context::class, Product_Variants_Creation_Context::class)->with_filter(new Tag_Filter('@applying_catalog_promotions&&@ui'))));