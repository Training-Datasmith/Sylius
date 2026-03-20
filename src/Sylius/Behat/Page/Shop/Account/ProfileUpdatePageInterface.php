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
namespace Sylius\Behat\Page\Shop\Account;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Friends_Of_Behat\Page_Object_Extension\Page\Page_Interface;
interface Profile_Update_Page_Interface extends Page_Interface
{
    public function specify_first_name(?string $first_name): void;
    public function specify_phone_number(?string $phone_number): void;
    public function get_phone_number(): string;
    public function specify_last_name(?string $last_name): void;
    public function specify_email(?string $email): void;
    public function save_changes(): void;
    /**
     * @throws ElementNotFoundException
     */
    public function check_validation_message_for(string $element, string $message): bool;
    public function subscribe_to_the_newsletter(): void;
    public function is_subscribed_to_the_newsletter(): bool;
}