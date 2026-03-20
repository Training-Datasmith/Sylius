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
use Sylius\Behat\Behaviour\Describes_It;
use Sylius\Behat\Behaviour\Names_It;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Behaviour\Toggles;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Symfony\Component\Routing\Router_Interface;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Names_It;
    use Specifies_Its_Field;
    use Describes_It;
    use Toggles;
    use Form_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected Autocomplete_Helper_Interface $autocomplete_helper)
    {
        parent::__construct($session, $mink_parameters, $router, $route_name);
    }
    protected function get_toggleable_element(): Node_Element
    {
        return $this->get_element('enabled');
    }
    public function allow_to_skip_shipping_step(): void
    {
        $this->get_document()->check_field('Skip shipping step if only one shipping method is available?');
    }
    public function allow_to_skip_payment_step(): void
    {
        $this->get_document()->check_field('Skip payment step if only one payment method is available?');
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements());
    }
}