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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Locale\Converter\Locale_Converter_Interface;
use Sylius\Component\Locale\Model\Locale_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Locale_Context implements Context
{
    /** @param RepositoryInterface<LocaleInterface> $localeRepository */
    public function __construct(private Locale_Converter_Interface $locale_name_converter, private Repository_Interface $locale_repository, private Shared_Storage_Interface $shared_storage)
    {
    }
    #[Transform(':language')]
    #[Transform(':localeCode')]
    #[Transform('/^"([^"]+)" locale$/')]
    #[Transform('/^in the "([^"]+)" locale$/')]
    public function cast_to_locale_code(string $locale_name): string
    {
        return $this->locale_name_converter->convert_name_to_code($locale_name);
    }
    #[Transform(':localeNameInItsLocale')]
    public function cast_to_its_locale_code(string $locale_name): string
    {
        return $this->locale_name_converter->convert_name_to_code($locale_name);
    }
    #[Transform(':localeNameInCurrentLocale')]
    public function cast_to_current_locale(string $locale_name): string
    {
        if ($this->shared_storage->has('current_locale_code')) {
            return $this->locale_name_converter->convert_name_to_code($locale_name);
        }
        return $locale_name;
    }
    #[Transform(':locale')]
    public function get_locale_by_name(string $name): Locale_Interface
    {
        $locale = $this->locale_repository->find_one_by(['code' => $this->locale_name_converter->convert_name_to_code($name)]);
        Assert::is_instance_of($locale, Locale_Interface::class, sprintf('Cannot find "%s" locale.', $name));
        return $locale;
    }
}