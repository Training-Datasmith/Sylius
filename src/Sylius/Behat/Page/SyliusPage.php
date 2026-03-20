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
namespace Sylius\Behat\Page;

use Behat\Mink\Element\Node_Element;
use Friends_Of_Behat\Page_Object_Extension\Page\Symfony_Page as BaseSymfonyPage;
use Sylius\Behat\Service\Driver_Helper;
abstract class Sylius_Page extends Base_Symfony_Page implements Sylius_Page_Interface
{
    protected function get_element(string $name, array $parameters = []): Node_Element
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        return parent::get_element($name, $parameters);
    }
    protected function blur(): void
    {
        $this->get_document()->find('css', 'body')->click();
    }
}