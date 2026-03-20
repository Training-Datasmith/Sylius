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
namespace Sylius\Bundle\Admin_Bundle\Context;

use Sylius\Component\Core\Model\Admin_User_Interface;
use Sylius\Component\Locale\Context\Locale_Context_Interface;
use Sylius\Component\Locale\Context\Locale_Not_Found_Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\Token_Storage_Interface;
final readonly class Admin_Based_Locale_Context implements Locale_Context_Interface
{
    public function __construct(private Token_Storage_Interface $token_storage)
    {
    }
    public function get_locale_code(): string
    {
        $token = $this->token_storage->get_token();
        if (null === $token) {
            throw new Locale_Not_Found_Exception();
        }
        $user = $token->get_user();
        if (!$user instanceof Admin_User_Interface) {
            throw new Locale_Not_Found_Exception();
        }
        return $user->get_locale_code();
    }
}