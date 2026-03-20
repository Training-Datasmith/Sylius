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
use Behat\Hook\After_Scenario;
final class Calendar_Context implements Context
{
    public function __construct(private $date_file_path)
    {
    }
    #[After_Scenario]
    public function delete_temporary_date(): void
    {
        if (file_exists($this->date_file_path)) {
            unlink($this->date_file_path);
        }
    }
}