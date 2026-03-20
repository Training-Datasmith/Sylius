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
use Sylius\Behat\Context\Ui\Admin\Channel_Pricing_Log_Entry_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_accessing_price_history'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.admin_user', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', Setup_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.currency', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage', Transform_Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.ui.admin.product_showpage', Channel_Pricing_Log_Entry_Context::class)->with_filter(new Tag_Filter('@accessing_price_history&&@ui'))));