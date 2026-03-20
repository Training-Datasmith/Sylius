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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_managing_exchange_rates'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.currency', 'sylius.behat.context.setup.exchange_rate')->with_contexts('sylius.behat.context.transform.currency', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.shared_storage')->with_contexts('sylius.behat.context.ui.admin.managing_exchange_rates', 'sylius.behat.context.ui.admin.notification', 'sylius.behat.context.ui.save')->with_filter(new Tag_Filter('@managing_exchange_rates&&@ui'))));