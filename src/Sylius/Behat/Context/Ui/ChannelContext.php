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
use Sylius\Behat\Page\Admin\Channel\Create_Page_Interface;
use Sylius\Behat\Page\Shop\Home_Page_Interface;
use Sylius\Behat\Page\Test_Plugin\Main_Page_Interface;
use Sylius\Behat\Service\Setter\Channel_Context_Setter_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Channel\Repository\Channel_Repository_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Webmozart\Assert\Assert;
final readonly class Channel_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Channel_Context_Setter_Interface $channel_context_setter, private Channel_Repository_Interface $channel_repository, private Create_Page_Interface $channel_create_page, private Home_Page_Interface $home_page, private Main_Page_Interface $plugin_main_page)
    {
    }
    #[When('I create a new channel :channelName')]
    public function i_create_new_channel(string $channel_name): void
    {
        $this->channel_create_page->open();
        $this->channel_create_page->name_it($channel_name);
        $this->channel_create_page->specify_code($channel_name);
        $this->channel_create_page->create();
        $channel = $this->channel_repository->find_one_by(['name' => $channel_name]);
        $this->shared_storage->set('channel', $channel);
    }
    #[When('/^I visit (this channel)\'s homepage$/')]
    #[When('/^I (?:am browsing|start browsing|try to browse|browse) (that channel)$/')]
    #[When('/^I (?:am browsing|start browsing|try to browse|browse) (?:|the )("[^"]+" channel)$/')]
    #[When('/^I (?:am browsing|start browsing|try to browse|browse) (?:|the )(channel "[^"]+")$/')]
    public function i_visit_channel_homepage(Channel_Interface $channel): void
    {
        $this->shared_storage->set('hostname', $channel->get_hostname());
        $this->channel_context_setter->set_channel($channel);
        $default_locale = $channel->get_default_locale();
        $this->home_page->open(['_locale' => $default_locale->get_code()]);
        $this->shared_storage->set('current_locale_code', $default_locale->get_code());
    }
    #[When('I visit plugin\'s main page')]
    public function visit_plugin_main_page(): void
    {
        $this->plugin_main_page->open();
    }
    #[Then('I should see a plugin\'s main page with content :content')]
    public function should_see_plugin_main_page_with_content(string $content): void
    {
        Assert::same($this->plugin_main_page->get_content(), $content);
    }
}