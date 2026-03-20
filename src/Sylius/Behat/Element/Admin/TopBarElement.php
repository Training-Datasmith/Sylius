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

use Sylius\Behat\Element\Sylius_Element;
class Top_Bar_Element extends Sylius_Element implements Top_Bar_Element_Interface
{
    public function has_avatar_in_main_bar(string $avatar_path): bool
    {
        return str_contains($this->get_avatar_image_path(), $avatar_path);
    }
    public function has_default_avatar_in_main_bar(): bool
    {
        return $this->get_avatar_image_path() === '';
    }
    protected function get_avatar_image_path(): string
    {
        $user_avatar = $this->get_element('user_avatar');
        return $user_avatar->get_attribute('data-test-user-avatar');
    }
    /**
     * @return array<string, string>
     */
    protected function get_defined_elements(): array
    {
        return ['user_avatar' => '[data-test-user-avatar]'];
    }
}