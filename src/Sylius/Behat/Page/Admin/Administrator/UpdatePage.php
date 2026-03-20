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
namespace Sylius\Behat\Page\Admin\Administrator;

use Sylius\Behat\Page\Admin\Crud\Update_Page as BaseUpdatePage;
class Update_Page extends Base_Update_Page implements Update_Page_Interface
{
    use Form_Aware_Trait;
    public function remove_avatar(): void
    {
        $this->get_element('button_delete_avatar')->click();
    }
    public function has_avatar(string $avatar_path): bool
    {
        $src_path = $this->get_avatar_image_path();
        return str_contains($src_path, $avatar_path);
    }
    public function change_locale(string $locale_code): void
    {
        $this->get_element('locale-switch')->select_option($locale_code);
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), $this->get_defined_form_elements(), ['button_delete_avatar' => '[data-test-delete-avatar-button]', 'locale-switch' => '[data-test-admin-locale-switch]']);
    }
    protected function get_avatar_image_path(): string
    {
        $avatar_image = $this->get_element('avatar_image');
        $image_path = $avatar_image->get_attribute('data-test-avatar-image');
        if (null === $image_path) {
            return '';
        }
        return $image_path;
    }
}