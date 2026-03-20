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
use Sylius\Behat\Page\Shop\Account\Address_Book\Create_Page;
use Sylius\Behat\Page\Shop\Account\Address_Book\Index_Page;
use Sylius\Behat\Page\Shop\Account\Address_Book\Update_Page;
use Sylius\Behat\Page\Shop\Account\Change_Password_Page;
use Sylius\Behat\Page\Shop\Account\Dashboard_Page;
use Sylius\Behat\Page\Shop\Account\Login_Page;
use Sylius\Behat\Page\Shop\Account\Order\Index_Page as OrderIndexPage;
use Sylius\Behat\Page\Shop\Account\Order\Show_Page as OrderShowPage;
use Sylius\Behat\Page\Shop\Account\Profile_Update_Page;
use Sylius\Behat\Page\Shop\Account\Register_Page;
use Sylius\Behat\Page\Shop\Account\Register_Thank_You_Page;
use Sylius\Behat\Page\Shop\Account\Request_Password_Reset_Page;
use Sylius\Behat\Page\Shop\Account\Reset_Password_Page;
use Sylius\Behat\Page\Shop\Account\Verification_Page;
use Sylius\Behat\Page\Shop\Account\Well_Known_Password_Change_Page;
use Symfony\Component\Dependency_Injection\Loader\Configurator\Container_Configurator;
use function Symfony\Component\Dependency_Injection\Loader\Configurator\service;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.behat.page.shop.account.address_book.create.class', Create_Page::class);
    $parameters->set('sylius.behat.page.shop.account.address_book.index.class', Index_Page::class);
    $parameters->set('sylius.behat.page.shop.account.address_book.update.class', Update_Page::class);
    $parameters->set('sylius.behat.page.shop.account.change_password.class', Change_Password_Page::class);
    $parameters->set('sylius.behat.page.shop.account.dashboard.class', Dashboard_Page::class);
    $parameters->set('sylius.behat.page.shop.account.login.class', Login_Page::class);
    $parameters->set('sylius.behat.page.shop.account.order.index.class', Order_Index_Page::class);
    $parameters->set('sylius.behat.page.shop.account.order.show.class', Order_Show_Page::class);
    $parameters->set('sylius.behat.page.shop.account.profile_update.class', Profile_Update_Page::class);
    $parameters->set('sylius.behat.page.shop.account.register.class', Register_Page::class);
    $parameters->set('sylius.behat.page.shop.account.register.thank_you.class', Register_Thank_You_Page::class);
    $parameters->set('sylius.behat.page.shop.account.request_password_reset.class', Request_Password_Reset_Page::class);
    $parameters->set('sylius.behat.page.shop.account.reset_password.class', Reset_Password_Page::class);
    $parameters->set('sylius.behat.page.shop.account.verify.class', Verification_Page::class);
    $parameters->set('sylius.behat.page.shop.account.well_known_password_change.class', Well_Known_Password_Change_Page::class);
    $services->set('sylius.behat.page.shop.account.address_book.create', '%sylius.behat.page.shop.account.address_book.create.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.address_book.index', '%sylius.behat.page.shop.account.address_book.index.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.address_book.update', '%sylius.behat.page.shop.account.address_book.update.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.change_password', '%sylius.behat.page.shop.account.change_password.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.shop.account.dashboard', '%sylius.behat.page.shop.account.dashboard.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.login', '%sylius.behat.page.shop.account.login.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor'), service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.shop.account.order.index', '%sylius.behat.page.shop.account.order.index.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor')]);
    $services->set('sylius.behat.page.shop.account.order.show', '%sylius.behat.page.shop.account.order.show.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.table_accessor')]);
    $services->set('sylius.behat.page.shop.account.profile_update', '%sylius.behat.page.shop.account.profile_update.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.register', '%sylius.behat.page.shop.account.register.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.register.thank_you', '%sylius.behat.page.shop.account.register.thank_you.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.request_password_reset', '%sylius.behat.page.shop.account.request_password_reset.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.reset_password', '%sylius.behat.page.shop.account.reset_password.class%')->parent('sylius.behat.symfony_page')->args([service('sylius.behat.shared_storage')]);
    $services->set('sylius.behat.page.shop.account.verify', '%sylius.behat.page.shop.account.verify.class%')->parent('sylius.behat.symfony_page');
    $services->set('sylius.behat.page.shop.account.well_known_password_change', '%sylius.behat.page.shop.account.well_known_password_change.class%')->parent('sylius.behat.symfony_page');
};