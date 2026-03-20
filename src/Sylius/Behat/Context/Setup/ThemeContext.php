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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Theme_Bundle\Configuration\Test\Test_Theme_Configuration_Manager_Interface;
use Sylius\Bundle\Theme_Bundle\Model\Theme_Interface;
use Sylius\Bundle\Theme_Bundle\Repository\Theme_Repository_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
final readonly class Theme_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Theme_Repository_Interface $theme_repository, private Object_Manager $channel_manager, private Test_Theme_Configuration_Manager_Interface $test_theme_configuration_manager)
    {
    }
    #[Given('the store has :themeName theme')]
    public function store_has_theme(string $theme_name): void
    {
        $this->test_theme_configuration_manager->add(['name' => $theme_name]);
        $this->shared_storage->set('theme', $this->theme_repository->find_one_by_name($theme_name));
    }
    #[Given('channel :channel uses :theme theme')]
    public function channel_uses_theme(Channel_Interface $channel, Theme_Interface $theme): void
    {
        $channel->set_theme_name($theme->get_name());
        $this->channel_manager->persist($channel);
        $this->channel_manager->flush();
        $this->shared_storage->set('channel', $channel);
        $this->shared_storage->set('theme', $theme);
    }
    #[Given('channel :channel does not use any theme')]
    public function channel_does_not_use_any_theme(Channel_Interface $channel): void
    {
        $channel->set_theme_name(null);
        $this->channel_manager->flush();
        $this->shared_storage->set('channel', $channel);
    }
    #[Given('/^(this theme) changes homepage template contents to "([^"]+)"$/')]
    public function theme_changes_homepage_template_contents(Theme_Interface $theme, string $contents): void
    {
        $this->change_template_contents('/templates/bundles/SyliusShopBundle/homepage/index.html.twig', $theme, $contents);
    }
    #[Given('/^(this theme) changes plugin main template\'s content to "([^"]+)"$/')]
    public function theme_changes_plugin_main_template_content(Theme_Interface $theme, string $content): void
    {
        $this->change_template_contents('/templates/bundles/SyliusTestPlugin/main.html.twig', $theme, $content);
    }
    private function change_template_contents(string $template_path, Theme_Interface $theme, string $contents): void
    {
        $file = rtrim($theme->get_path(), '/') . $template_path;
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($file, $contents);
    }
}