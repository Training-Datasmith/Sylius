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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_customer_login', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.mailer')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.customer', 'sylius.behat.context.setup.locale', 'sylius.behat.context.setup.user')->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.locale', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.user')->with_contexts('sylius.behat.context.api.debug', 'sylius.behat.context.api.email', 'sylius.behat.context.api.shop.customer', 'sylius.behat.context.api.shop.login')->with_filter(new Tag_Filter('@customer_login&&@api'))));