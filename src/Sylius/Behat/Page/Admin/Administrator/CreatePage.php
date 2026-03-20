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
namespace Sylius\Behat\Page\Admin\Administrator;

use Behat\Mink\Session;
use Sylius\Behat\Page\Admin\Crud\Create_Page as BaseCreatePage;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Symfony\Component\Routing\Router_Interface;
class Create_Page extends Base_Create_Page implements Create_Page_Interface
{
    use Form_Aware_Trait;
    public function __construct(Session $session, $mink_parameters, Router_Interface $router, string $route_name, protected Shared_Storage_Interface $shared_storage)
    {
        parent::__construct($session, $mink_parameters, $router, $route_name);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements());
    }
}