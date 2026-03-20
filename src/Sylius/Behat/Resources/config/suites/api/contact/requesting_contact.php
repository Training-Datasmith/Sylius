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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_customer_requesting_contact', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.mailer')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.shop_api_security', 'sylius.behat.context.setup.user')->with_contexts('sylius.behat.context.transform.shared_storage')->with_contexts('sylius.behat.context.api.debug', 'sylius.behat.context.api.email', 'sylius.behat.context.api.shop.contact')->with_filter(new Tag_Filter('@requesting_contact&&@api'))));