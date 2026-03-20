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
final class Question_Factory implements Question_Factory_Interface
{
    public function create_email(): Question
    {
        $question = new Question('Email');
        $question->set_validator(function (?string $email): string {
            if ($email === null || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('The email address provided is invalid. Please try again.');
            }
            return $email;
        });
        $question->set_max_attempts(3);
        return $question;
    }
    public function create_with_not_null_validator(string $asked_question, bool $hidden = false): Question
    {
        $question = new Question($asked_question);
        $question->set_validator(function (?string $value): string {
            if ($value === null) {
                throw new \InvalidArgumentException('The value cannot be empty.');
            }
            return $value;
        });
        $question->set_max_attempts(3);
        $question->set_hidden($hidden);
        return $question;
    }
}