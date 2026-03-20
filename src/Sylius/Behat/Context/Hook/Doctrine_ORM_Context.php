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
namespace Sylius\Behat\Context\Hook;

use Behat\Behat\Context\Context;
use Behat\Hook\Before_Scenario;
use Doctrine\Common\Data_Fixtures\Purger\Orm_Purger;
use Doctrine\ORM\Entity_Manager_Interface;
final readonly class Doctrine_Orm_Context implements Context
{
    public function __construct(private Entity_Manager_Interface $entity_manager)
    {
    }
    #[Before_Scenario]
    public function purge_database(): void
    {
        $this->entity_manager->get_connection()->get_configuration()->set_sql_logger(null);
        $purger = new Orm_Purger($this->entity_manager);
        $purger->purge();
        $this->entity_manager->clear();
    }
}