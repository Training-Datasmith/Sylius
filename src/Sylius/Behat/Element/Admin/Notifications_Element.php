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
namespace Sylius\Behat\Element\Admin;

use Behat\Mink\Element\Node_Element;
use Sylius\Behat\Element\Sylius_Element;
use Sylius\Behat\Service\Driver_Helper;
class Notifications_Element extends Sylius_Element implements Notifications_Element_Interface
{
    public function has_notification(string $type, string $message): bool
    {
        $flashes_container = $this->get_element('flashes_container');
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $flashes_container->wait_for(5, fn() => $flashes_container->is_visible());
        }
        /** @var array<NodeElement> $flashes */
        $flashes = $flashes_container->find_all('css', '[data-test-sylius-flash-message]');
        foreach ($flashes as $flash) {
            if (str_contains((string) $flash->get_text(), $message) && $flash->get_attribute('data-test-sylius-flash-message-type') === $type) {
                return true;
            }
        }
        return false;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['flashes_container' => '[data-test-sylius-flashes-container]']);
    }
}