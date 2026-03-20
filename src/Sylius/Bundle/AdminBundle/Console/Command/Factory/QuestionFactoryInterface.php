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
namespace Sylius\Bundle\Admin_Bundle\Console\Command\Factory;

use Symfony\Component\Console\Question\Question;
interface Question_Factory_Interface
{
    public function create_email(): Question;
    public function create_with_not_null_validator(string $asked_question, bool $hidden = false): Question;
}