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
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Locale\Converter\Locale_Converter_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Locale_Context implements Context
{
    /**
     * @param FactoryInterface<LocaleInterface> $localeFactory
     * @param RepositoryInterface<LocaleInterface> $localeRepository
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Locale_Converter_Interface $locale_converter, private Factory_Interface $locale_factory, private Repository_Interface $locale_repository, private Object_Manager $locale_manager, private Object_Manager $channel_manager)
    {
    }
    #[Given('the store has locale :localeCode')]
    #[Given('the store is( also) available in :localeCode')]
    #[Given('the locale :localeCode is enabled')]
    public function the_store_has_locale(string $locale_code): void
    {
        $locale = $this->provide_locale($locale_code);
        $this->save_locale($locale);
    }
    #[Given('the store has many locales')]
    public function the_store_has_many_locales(): void
    {
        $this->the_store_has_locale('en_US');
        $this->the_store_has_locale('fr_FR');
        $this->the_store_has_locale('de_DE');
        $this->the_store_has_locale('es_ES');
        $this->the_store_has_locale('pl_PL');
        $this->the_store_has_locale('pt_PT');
        $this->the_store_has_locale('uk_UA');
        $this->the_store_has_locale('ja_JP');
        $this->the_store_has_locale('zh_CN');
        $this->the_store_has_locale('bg_BG');
        $this->the_store_has_locale('da_DK');
    }
    #[Given('the locale :localeCode does not exist in the store')]
    public function the_store_does_not_have_locale(string $locale_code): void
    {
        /** @var LocaleInterface $locale */
        $locale = $this->locale_repository->find_one_by(['code' => $locale_code]);
        if (null !== $locale) {
            $this->locale_repository->remove($locale);
        }
    }
    #[Given('/^(that channel) allows to shop using the "([^"]+)" locale$/')]
    #[Given('/^(that channel) allows to shop using "([^"]+)" and "([^"]+)" locales$/')]
    #[Given('/^(that channel) allows to shop using "([^"]+)", "([^"]+)" and "([^"]+)" locales$/')]
    #[Given('/^(this channel) allows to shop using the "([^"]+)" locale$/')]
    #[Given('/^(this channel) allows to shop using "([^"]+)" and "([^"]+)" locales$/')]
    public function that_channel_allows_to_shop_using_and_locales(Channel_Interface $channel, ...$locales_names): void
    {
        foreach ($channel->get_locales() as $locale) {
            $channel->remove_locale($locale);
        }
        foreach ($locales_names as $locale_name) {
            $channel->add_locale($this->provide_locale($this->locale_converter->convert_name_to_code($locale_name)));
        }
        $this->channel_manager->flush();
    }
    #[Given('/^(it) uses the "([^"]+)" locale by default$/')]
    #[Given('/^(this channel) uses the "([^"]+)" locale as default$/')]
    public function it_uses_the_locale_by_default(Channel_Interface $channel, string $locale_name): void
    {
        $locale = $this->provide_locale($this->locale_converter->convert_name_to_code($locale_name));
        $this->locale_manager->flush();
        $channel->add_locale($locale);
        $channel->set_default_locale($locale);
        $this->channel_manager->flush();
    }
    private function create_locale(string $locale_code): Locale_Interface
    {
        /** @var LocaleInterface $locale */
        $locale = $this->locale_factory->create_new();
        $locale->set_code($locale_code);
        return $locale;
    }
    private function provide_locale(string $locale_code): Locale_Interface
    {
        $locale = $this->locale_repository->find_one_by(['code' => $locale_code]);
        if (null === $locale) {
            $locale = $this->create_locale($locale_code);
            $this->locale_repository->add($locale);
        }
        return $locale;
    }
    private function save_locale(Locale_Interface $locale): void
    {
        $this->shared_storage->set('locale', $locale);
        $this->locale_repository->add($locale);
    }
}