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
use Sylius\Behat\Context\Setup\Catalog_Promotion_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_viewing_price_history', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.admin_api_security', 'sylius.behat.context.setup.admin_user', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.product', Catalog_Promotion_Context::class)->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.product_variant', 'sylius.behat.context.transform.shared_storage')->with_contexts('sylius.behat.context.api.admin.channel_pricing_log_entry', 'sylius.behat.context.api.admin.managing_product_variants', 'sylius.behat.context.api.admin.save', 'sylius.behat.context.api.debug')->with_filter(new Tag_Filter('@viewing_price_history&&@api'))));