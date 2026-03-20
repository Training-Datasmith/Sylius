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
namespace Sylius\Behat\Page\Admin\Tax_Rate;

interface Form_Aware_Interface
{
    public function name_it(string $name): void;
    public function specify_amount(string $amount): void;
    public function specify_start_date(\DateTimeInterface $start_date): void;
    public function specify_end_date(\DateTimeInterface $end_date): void;
    public function choose_zone(string $name): void;
    public function choose_category(string $name): void;
    public function choose_calculator(string $name): void;
}