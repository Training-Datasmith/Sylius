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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Administrator\Update_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Admin_User_Interface;
final readonly class Managing_Administrator_Locales_Context implements Context
{
    public function __construct(private Update_Page_Interface $update_page, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[When('I change my locale to :localeCode')]
    public function i_change_my_locale_to(string $locale_code): void
    {
        /** @var AdminUserInterface $adminUser */
        $admin_user = $this->shared_storage->get('administrator');
        $this->update_page->open(['id' => $admin_user->get_id()]);
        $this->update_page->change_locale($locale_code);
        $this->update_page->save_changes();
    }
}