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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_viewing_exchange_rates', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.currency', 'sylius.behat.context.setup.exchange_rate')->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.currency', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.shared_storage')->with_contexts('sylius.behat.context.api.debug', 'sylius.behat.context.api.shop.channel', 'sylius.behat.context.api.shop.currency', 'sylius.behat.context.api.shop.exchange_rate')->with_filter(new Tag_Filter('@viewing_exchange_rates&&@api'))));