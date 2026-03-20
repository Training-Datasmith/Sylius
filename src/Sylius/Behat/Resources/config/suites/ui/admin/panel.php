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
use Sylius\Behat\Context\Ui\Admin\Product_Creation_Context;
use Sylius\Behat\Context\Ui\Admin\Removing_Product_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_panel'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.admin_user', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.taxonomy')->with_contexts('sylius.behat.context.transform.admin', 'sylius.behat.context.transform.channel', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.taxon')->with_contexts('sylius.behat.context.ui.admin.browsing_product_variants', 'sylius.behat.context.ui.admin.managing_products', 'sylius.behat.context.ui.save', 'sylius.behat.context.ui.shop.browsing_product', Product_Creation_Context::class, Removing_Product_Context::class)->with_filter(new Tag_Filter('@admin_panel&&@ui'))));