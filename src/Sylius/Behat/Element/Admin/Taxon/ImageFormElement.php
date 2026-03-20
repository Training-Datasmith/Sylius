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
namespace Sylius\Behat\Element\Admin\Taxon;

use Behat\Mink\Exception\Element_Not_Found_Exception;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
class Image_Form_Element extends Base_Form_Element implements Image_Form_Element_Interface
{
    public function attach_image(string $path, ?string $type = null): void
    {
        $this->get_element('add_image')->press();
        $this->wait_for_form_update();
        $last_image = $this->get_element('last_image');
        if (null !== $type) {
            $last_image->fill_field('Type', $type);
        }
        $files_path = $this->get_parameter('files_path');
        $last_image->find('css', '[data-test-file]')->attach_file($files_path . $path);
    }
    public function change_image_with_type(string $type, string $path): void
    {
        $image = $this->get_element('image_with_type', ['%type%' => $type]);
        $files_path = $this->get_parameter('files_path');
        $image->find('css', '[data-test-file]')->attach_file($files_path . $path);
    }
    public function modify_first_image_type(string $type): void
    {
        $this->get_element('first_image')->fill_field('Type', $type);
    }
    public function remove_image_with_type(string $type): void
    {
        $this->get_element('delete_image', ['%type%' => $type])->press();
        $this->wait_for_form_update();
    }
    public function remove_first_image(): void
    {
        $this->get_element('first_image')->find('css', '[data-test-delete-image]')->press();
        $this->wait_for_form_update();
    }
    public function is_image_with_type_displayed(string $type): bool
    {
        try {
            $image = $this->get_element('image_with_type', ['%type%' => $type]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        $image_url = $image->get_attribute('data-test-image-url');
        $original_url = $this->get_driver()->get_current_url();
        $this->get_driver()->visit($image_url);
        $status_code = $this->get_driver()->get_status_code();
        $this->get_driver()->visit($original_url);
        return in_array($status_code, [200, 304], true);
    }
    public function count_images(): int
    {
        return count($this->get_element('images')->find_all('css', '[data-test-image]'));
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_image' => '[data-test-images] [data-test-add-image]', 'delete_image' => '[data-test-images] [data-test-image][data-test-type="%type%"] [data-test-delete-image]', 'form' => '[data-live-name-value="sylius_admin:taxon:form"]', 'first_image' => '[data-test-images] [data-test-image]:first-child', 'image_with_type' => '[data-test-images] [data-test-image][data-test-type="%type%"]', 'images' => '[data-test-images]', 'last_image' => '[data-test-images] [data-test-image]:last-child']);
    }
}