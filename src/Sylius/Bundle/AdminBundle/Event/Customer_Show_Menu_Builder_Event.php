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
namespace Sylius\Bundle\Admin_Bundle\Event;

use Knp\Menu\Factory_Interface;
use Knp\Menu\Item_Interface;
use Sylius\Bundle\Ui_Bundle\Menu\Event\Menu_Builder_Event;
use Sylius\Component\Core\Model\Customer_Interface;
/** TODO: possibly remove */
class Customer_Show_Menu_Builder_Event extends Menu_Builder_Event
{
    public function __construct(Factory_Interface $factory, Item_Interface $menu, private readonly Customer_Interface $customer)
    {
        parent::__construct($factory, $menu);
    }
    public function get_customer(): Customer_Interface
    {
        return $this->customer;
    }
}