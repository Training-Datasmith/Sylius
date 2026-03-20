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
namespace Sylius\Behat\Service\Checker;

use Sylius\Behat\Service\Provider\Email_Messages_Provider_Interface;
use Symfony\Component\Mime\Email;
use Webmozart\Assert\Assert;
final readonly class Email_Checker implements Email_Checker_Interface
{
    public function __construct(private Email_Messages_Provider_Interface $email_messages_provider)
    {
    }
    public function has_recipient(string $recipient): bool
    {
        $messages = $this->email_messages_provider->provide();
        foreach ($messages as $email) {
            if ($this->is_message_to($email, $recipient)) {
                return true;
            }
        }
        return false;
    }
    public function has_message_to(string $message, string $recipient): bool
    {
        $this->assert_recipient_is_valid($recipient);
        $messages = $this->email_messages_provider->provide();
        foreach ($messages as $email) {
            if ($this->is_message_to($email, $recipient)) {
                $email_text_content = trim((string) preg_replace('/\n+\s+/', ' ', strip_tags((string) $email->get_html_body())));
                if (str_contains($email_text_content, $message)) {
                    return true;
                }
            }
        }
        return false;
    }
    public function count_messages_to(string $recipient): int
    {
        $this->assert_recipient_is_valid($recipient);
        $messages_count = 0;
        $messages = $this->email_messages_provider->provide();
        foreach ($messages as $email) {
            if ($this->is_message_to($email, $recipient)) {
                ++$messages_count;
            }
        }
        return $messages_count;
    }
    private function is_message_to(Email $message, string $recipient): bool
    {
        foreach ($message->get_to() as $to_recipient) {
            if ($recipient === $to_recipient->get_address()) {
                return true;
            }
        }
        return false;
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function assert_recipient_is_valid(string $recipient): void
    {
        Assert::not_empty($recipient, 'The recipient cannot be empty.');
        Assert::not_eq(false, filter_var($recipient, \FILTER_VALIDATE_EMAIL), 'Given recipient is not a valid email address.');
    }
}