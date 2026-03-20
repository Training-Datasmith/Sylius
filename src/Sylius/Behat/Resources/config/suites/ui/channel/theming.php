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
return (new Config())->with_profile((new Profile('default'))->with_suite((new Suite('ui_theming'))->with_contexts('sylius.behat.context.hook.bad_gateway', 'sylius.behat.context.hook.doctrine_orm', 'sylius.behat.context.hook.session', 'sylius.behat.context.hook.test_theme')->with_contexts('sylius.behat.context.setup.admin_security', 'sylius.behat.context.setup.channel', 'sylius.behat.context.setup.theme')->with_contexts('sylius.behat.context.transform.channel', 'sylius.behat.context.transform.shared_storage', 'sylius.behat.context.transform.theme')->with_contexts('sylius.behat.context.ui.channel', 'sylius.behat.context.ui.theme')->with_filter(new Tag_Filter('@theming&&@ui'))));