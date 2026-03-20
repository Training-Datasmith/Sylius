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
namespace Sylius\Bundle\Admin_Bundle\Controller;

use Sylius\Component\Channel\Repository\Channel_Repository_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Routing\Router_Interface;
use Twig\Environment;
use Webmozart\Assert\Assert;
final readonly class Dashboard_Controller
{
    public function __construct(private Channel_Repository_Interface $channel_repository, private Environment $templating_engine, private Router_Interface $router)
    {
    }
    public function __invoke(Request $request): Response
    {
        /** @var ChannelInterface|null $channel */
        $channel = $this->find_channel_by_code_or_find_first($request->query->has('channel') ? (string) $request->query->get('channel') : null);
        if (null === $channel) {
            return new Redirect_Response($this->router->generate('sylius_admin_channel_create'));
        }
        return new Response($this->templating_engine->render('@SyliusAdmin/dashboard/index.html.twig', ['channel' => $channel]));
    }
    private function find_channel_by_code_or_find_first(?string $channel_code): ?Channel_Interface
    {
        if (null !== $channel_code) {
            $channel = $this->channel_repository->find_one_by_code($channel_code);
            Assert::null_or_is_instance_of($channel, Channel_Interface::class);
            return $channel;
        }
        $channel = $this->channel_repository->find_by([], ['id' => 'ASC'], 1)[0] ?? null;
        Assert::null_or_is_instance_of($channel, Channel_Interface::class);
        return $channel;
    }
}