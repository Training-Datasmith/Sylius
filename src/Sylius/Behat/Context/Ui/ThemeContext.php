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
namespace Sylius\Behat\Context\Ui;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Page\Admin\Channel\Index_Page_Interface;
use Sylius\Behat\Page\Admin\Channel\Update_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Theme_Bundle\Model\Theme_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Webmozart\Assert\Assert;
final readonly class Theme_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Index_Page_Interface $channel_index_page, private Update_Page_Interface $channel_update_page, private Home_Page_Interface $home_page)
    {
    }
    #[When('I set :channel channel theme to :theme')]
    public function i_set_channel_theme_to(Channel_Interface $channel, Theme_Interface $theme): void
    {
        $this->channel_update_page->open(['id' => $channel->get_id()]);
        $this->channel_update_page->set_theme($theme->get_name());
        $this->channel_update_page->save_changes();
        $this->shared_storage->set('channel', $channel);
        $this->shared_storage->set('theme', $theme);
    }
    #[When('/^I unset theme on (that channel)$/')]
    public function i_unset_theme_on_channel(Channel_Interface $channel): void
    {
        $this->channel_update_page->open(['id' => $channel->get_id()]);
        $this->channel_update_page->set_theme('');
        $this->channel_update_page->save_changes();
    }
    #[Then('/^(that channel) should not use any theme$/')]
    public function channel_should_not_use_any_theme(Channel_Interface $channel): void
    {
        $this->channel_index_page->open();
        Assert::same($this->channel_index_page->get_used_theme_name($channel->get_code()), 'Default');
    }
    #[Then('/^(that channel) should use (that theme)$/')]
    public function channel_should_use_theme(Channel_Interface $channel, Theme_Interface $theme): void
    {
        $this->channel_index_page->open();
        Assert::same($this->channel_index_page->get_used_theme_name($channel->get_code()), $theme->get_name());
    }
    #[Then('/^I should see a homepage from ((?:this|that) theme)$/')]
    public function i_should_see_themed_homepage(Theme_Interface $theme): void
    {
        $content = file_get_contents(rtrim($theme->get_path(), '/') . '/templates/bundles/SyliusShopBundle/homepage/index.html.twig');
        Assert::same($this->home_page->get_content(), $content);
    }
    #[Then('I should not see a homepage from :theme theme')]
    public function i_should_not_see_themed_homepage(Theme_Interface $theme): void
    {
        $content = file_get_contents(rtrim($theme->get_path(), '/') . '/templates/bundles/SyliusShopBundle/homepage/index.html.twig');
        Assert::not_same($this->home_page->get_content(), $content);
    }
}