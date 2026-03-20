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
use Sylius\Behat\Context\Api\Admin\Removing_Taxon_Context;
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_managing_promotions', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.admin_api_security', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.customer_group', 'sylius.behat.context.setup.locale', 'sylius.behat.context.setup.order', 'sylius.behat.context.setup.payment', 'sylius.behat.context.setup.product', 'sylius.behat.context.setup.product_taxon', 'sylius.behat.context.setup.promotion', 'sylius.behat.context.setup.shipping', 'sylius.behat.context.setup.taxonomy')->with_contexts('sylius.behat.context.transform.address', 'sylius.behat.context.transform.channel', 'sylius.behat.context.transform.customer', 'sylius.behat.context.transform.customer_group', 'sylius.behat.context.transform.date_time', 'sylius.behat.context.transform.lexical', 'sylius.behat.context.transform.locale', 'sylius.behat.context.transform.payment', 'sylius.behat.context.transform.product', 'sylius.behat.context.transform.promotion', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.shipping_method', 'sylius.behat.context.transform.taxon')->with_contexts('sylius.behat.context.api.admin.managing_promotions', 'sylius.behat.context.api.admin.removing_product', 'sylius.behat.context.api.admin.response', 'sylius.behat.context.api.admin.save', 'sylius.behat.context.api.admin.translation', 'sylius.behat.context.api.debug', Removing_Taxon_Context::class)->with_filter(new Tag_Filter('@managing_promotions&&@api'))));