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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_viewing_product_variants', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product')->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_option', 'sylius.behat.context.transform.product_option_value', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage')->with_contexts('sylius.behat.context.api.debug', 'sylius.behat.context.api.shop.channel', 'sylius.behat.context.api.shop.product', 'sylius.behat.context.api.shop.product_variant')->with_filter(new Tag_Filter('@viewing_product_variants&&@api'))));