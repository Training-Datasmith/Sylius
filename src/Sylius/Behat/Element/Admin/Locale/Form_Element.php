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
namespace Sylius\Behat\Element\Admin\Locale;

use Behat\Mink\Session;
use Friends_Of_Behat\Symfony_Extension\Mink\Mink_Parameters;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    public function __construct(Session $session, array|Mink_Parameters $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function choose_locale(string $locale_name): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('code')->get_xpath(), $locale_name);
    }
    public function is_locale_available(string $locale_name): bool
    {
        $elements = $this->autocomplete_helper->search($this->get_driver(), $this->get_element('code')->get_xpath(), $locale_name);
        foreach ($elements as $element) {
            if (str_contains((string) $element, $locale_name)) {
                return true;
            }
        }
        return false;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]']);
    }
}