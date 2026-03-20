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
use Sylius\Behat\Context\Domain\Managing_Price_History_Context;
use Sylius\Behat\Context\Setup\Price_History_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('domain_managing_price_history'))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.calendar', Price_History_Context::class)->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.shared_storage')->with_contexts(Managing_Price_History_Context::class)->with_filter(new Tag_Filter('@managing_price_history&&@domain'))));