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
namespace Sylius\Behat\Element\Product\Show_Page;

use Sylius\Behat\Element\Sylius_Element;
class Options_Element extends Sylius_Element implements Options_Element_Interface
{
    public function is_option_defined(string $option_name): bool
    {
        $options = $this->get_element('options');
        return $options->has('css', sprintf('div:contains("%s")', $option_name));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['options' => '[data-test-options]']);
    }
}