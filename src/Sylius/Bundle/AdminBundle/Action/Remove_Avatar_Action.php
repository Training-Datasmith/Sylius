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
namespace Sylius\Bundle\Admin_Bundle\Action;

use Sylius\Component\Core\Model\Avatar_Image;
use Sylius\Component\Core\Repository\Avatar_Image_Repository_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Symfony\Component\Routing\Router_Interface;
use Symfony\Component\Security\Csrf\Csrf_Token;
use Symfony\Component\Security\Csrf\Csrf_Token_Manager_Interface;
final readonly class Remove_Avatar_Action
{
    public function __construct(private Avatar_Image_Repository_Interface $avatar_repository, private Router_Interface $router, private Csrf_Token_Manager_Interface $csrf_token_manager)
    {
    }
    public function __invoke(Request $request): Response
    {
        $user_id = $request->attributes->get('id', '');
        if (!$this->csrf_token_manager->is_token_valid(new Csrf_Token($user_id, (string) $request->query->get('_csrf_token', '')))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }
        /** @var AvatarImage|null $avatar */
        $avatar = $this->avatar_repository->find_one_by_owner_id($user_id);
        if (null !== $avatar) {
            $this->avatar_repository->remove($avatar);
        }
        return new Redirect_Response($this->router->generate('sylius_admin_admin_user_update', ['id' => $user_id]));
    }
}