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
namespace Sylius\Behat\Element\Admin\Catalog_Promotion;

use Sylius\Component\Core\Model\Channel_Interface;
interface Filter_Element_Interface
{
    public function choose_channel(Channel_Interface $channel): void;
    public function choose_enabled(): void;
    public function choose_state(string $state): void;
    public function specify_start_date_from(string $date): void;
    public function specify_start_date_to(string $date): void;
    public function specify_end_date_from(string $date): void;
    public function specify_end_date_to(string $date): void;
    public function filter(): void;
}