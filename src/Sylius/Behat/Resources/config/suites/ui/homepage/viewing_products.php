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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_homepage'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.shop_security', 'sylius.behat.context.setup.taxonomy')->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.taxon')->with_contexts('sylius.behat.context.ui.channel', 'sylius.behat.context.ui.shop.cart', 'sylius.behat.context.ui.shop.homepage')->with_filter(new Tag_Filter('@homepage&&@ui'))));