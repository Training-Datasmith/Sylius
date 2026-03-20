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
namespace Sylius\Behat\Context\Ui\Admin\Helper;

trait Navigation_Trait
{
    public function has_show_page_button(): bool
    {
        return $this->has_element('show_' . $this->get_resource_name() . '_button');
    }
    public function switch_to_show_page(): void
    {
        $this->get_element('show_' . $this->get_resource_name() . '_button')->click();
    }
    public function has_edit_page_button(): bool
    {
        return $this->has_element('edit_' . $this->get_resource_name() . '_button');
    }
    public function switch_to_edit_page(): void
    {
        $this->get_element('edit_' . $this->get_resource_name() . '_button')->click();
    }
    abstract protected function get_resource_name(): string;
}