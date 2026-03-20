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
namespace Sylius\Behat\Page\Admin\Channel;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Checks_Code_Immutability;
    use Toggles;
    use Form_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $route_name);
    }
    public function get_locales(): array
    {
        return array_map(fn(Node_Element $element) => $element->get_text(), $this->get_element('locales')->find_all('css', 'option:selected'));
    }
    public function get_currencies(): array
    {
        return array_map(fn(Node_Element $element) => $element->get_text(), $this->get_element('currencies')->find_all('css', 'option:selected'));
    }
    public function get_default_tax_zone(): ?string
    {
        return $this->get_element('default_tax_zone')->find('css', 'option:selected')?->get_text();
    }
    public function is_base_currency_disabled(): bool
    {
        return $this->get_element('base_currency')->has_attribute('disabled');
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function get_toggleable_element(): Node_Element
    {
        return $this->get_element('enabled');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements());
    }
}