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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Channel\Repository\Channel_Repository_Interface;
use Sylius\Component\Core\Model\Channel_Interface;
use Webmozart\Assert\Assert;
final readonly class Channel_Context implements Context
{
    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(private Channel_Repository_Interface $channel_repository)
    {
    }
    #[Transform('/^channel "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" channel/')]
    #[Transform('/^channel to "([^"]+)"$/')]
    #[Transform(':channel')]
    public function get_channel_by_name(string $channel_name)
    {
        $channels = $this->channel_repository->find_by_name($channel_name);
        Assert::eq(count($channels), 1, sprintf('%d channels has been found with name "%s".', count($channels), $channel_name));
        return $channels[0];
    }
    /**
     * @return array<ChannelInterface>
     */
    #[Transform('all channels')]
    public function get_all_channels(): array
    {
        return $this->channel_repository->find_all();
    }
}