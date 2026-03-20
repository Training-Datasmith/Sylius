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
namespace Sylius\Behat\Service\Accessor;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Service\Driver_Helper;
final readonly class Notification_Accessor implements Notification_Accessor_Interface
{
    public function __construct(private Session $session, private string $locator)
    {
    }
    public function get_message_elements(): array
    {
        Driver_Helper::wait_for_element($this->session, $this->locator);
        $message_elements = $this->session->get_page()->find_all('css', $this->locator);
        if (empty($message_elements)) {
            throw new Element_Not_Found_Exception($this->session->get_driver(), 'message element', 'css', $this->locator);
        }
        return $message_elements;
    }
}