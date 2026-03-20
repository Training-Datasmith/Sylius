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
namespace Sylius\Behat\Service\Context;

use Sylius\Component\Core\Repository\Order_Repository_Interface;
use Sylius\Component\Order\Context\Cart_Context_Interface;
use Sylius\Component\Order\Context\Cart_Not_Found_Exception;
use Sylius\Component\Order\Model\Order_Interface;
final readonly class Guest_Cart_Context implements Cart_Context_Interface
{
    public function __construct(private Order_Repository_Interface $order_repository, private string $guest_cart_token_file_path)
    {
    }
    public function get_cart(): Order_Interface
    {
        if (!file_exists($this->guest_cart_token_file_path)) {
            throw new Cart_Not_Found_Exception(sprintf('The file at "%s" could not be found.', $this->guest_cart_token_file_path));
        }
        $token = file_get_contents($this->guest_cart_token_file_path);
        $cart = $this->order_repository->find_cart_by_token_value($token);
        if (null === $cart) {
            throw new Cart_Not_Found_Exception();
        }
        return $cart;
    }
}