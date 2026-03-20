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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('api_administrator_security', ['javascript' => false]))->with_contexts('sylius.behat.context.hook.doctrine_orm')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.admin_user', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.user')->with_contexts('sylius.behat.context.transform.admin', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.user')->with_contexts('sylius.behat.context.api.admin.login', 'sylius.behat.context.api.admin.managing_administrators', 'sylius.behat.context.api.debug', 'sylius.behat.context.api.email')->with_filter(new Tag_Filter('@administrator_security&&@api'))));