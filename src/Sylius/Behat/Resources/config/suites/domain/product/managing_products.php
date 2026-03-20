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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('domain_managing_products'))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.currency', 'sylius.behat.context.setup.locale', 'sylius.behat.context.setup.order', 'sylius.behat.context.setup.payment', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.product_review', 'sylius.behat.context.setup.shipping')->with_contexts('sylius.behat.context.transform.address', 'sylius.behat.context.transform.currency', 'sylius.behat.context.transform.customer', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.locale', 'sylius.behat.context.transform.payment', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.shipping_method')->with_contexts('sylius.behat.context.domain.managing_products', 'sylius.behat.context.domain.notification', 'sylius.behat.context.domain.security')->with_filter(new Tag_Filter('@managing_products&&@domain'))));