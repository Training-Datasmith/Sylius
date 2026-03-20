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
use Symfony\Contracts\Translation\Translator_Interface;
final readonly class Shipping_Calculator_Context implements Context
{
    public function __construct(private array $shipping_calculators, private Translator_Interface $translator)
    {
    }
    #[Transform(':shippingCalculator')]
    public function get_shipping_calculator_by_name(string $shipping_calculator): string
    {
        $flipped_calculators = array_flip(array_map(fn(string $translation_key): string => $this->translator->trans($translation_key), $this->shipping_calculators));
        return $flipped_calculators[$shipping_calculator];
    }
}