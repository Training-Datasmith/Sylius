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
namespace Sylius\Behat\Element\Admin\Product;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Core\Model\Product_Variant_Interface;
class Media_Form_Element extends Base_Form_Element implements Media_Form_Element_Interface
{
    public function __construct(Session $session, $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function attach_image(string $path, ?string $type = null, ?Product_Variant_Interface $product_variant = null): void
    {
        $this->change_tab();
        $this->get_element('add_image')->click();
        $this->wait_for_form_update();
        $images = $this->get_element('images');
        $images_subform = $images->find_all('css', '[data-test-image-subform]');
        $image_subform = end($images_subform);
        if (null !== $type) {
            $image_subform->fill_field('Type', $type);
        }
        if (null !== $product_variant) {
            $this->autocomplete_helper->select_by_name($this->get_driver(), $image_subform->find('css', '[data-test-product-variant]')->get_xpath(), $product_variant->get_name());
        }
        $files_path = $this->get_parameter('files_path');
        $image_subform->find('css', '[data-test-file]')->attach_file($files_path . $path);
    }
    public function change_image_with_type(string $type, string $path): void
    {
        $files_path = $this->get_parameter('files_path');
        $image_subform = $this->get_element('image_subform_with_type', ['%type%' => $type]);
        $image_subform->find('css', '[data-test-file]')->attach_file($files_path . $path);
    }
    public function remove_image_with_type(string $type): void
    {
        $this->change_tab();
        $image_subform = $this->get_element('image_subform_with_type', ['%type%' => $type]);
        $image_subform->find('css', '[data-test-image-delete]')->click();
        $this->wait_for_form_update();
    }
    public function remove_first_image(): void
    {
        $this->change_tab();
        $first_subform = $this->get_first_image_subform();
        $first_subform->find('css', '[data-test-image-delete]')->click();
        $this->wait_for_form_update();
    }
    public function has_image_with_type(string $type): bool
    {
        $this->change_tab();
        try {
            $image_subform = $this->get_element('image_subform_with_type', ['%type%' => $type]);
        } catch (Element_Not_Found_Exception) {
            return false;
        }
        $image_url = $image_subform->get_attribute('data-test-image-url');
        $original_url = $this->get_driver()->get_current_url();
        $this->get_driver()->visit($image_url);
        $status_code = $this->get_driver()->get_status_code();
        $this->get_driver()->visit($original_url);
        return in_array($status_code, [200, 304], true);
    }
    public function has_image_with_variant(Product_Variant_Interface $product_variant): bool
    {
        $selected_variant_name = $this->get_first_image_selected_variant_name();
        if (null === $selected_variant_name) {
            return false;
        }
        return str_contains($selected_variant_name, (string) $product_variant->get_name());
    }
    public function get_first_image_selected_variant_name(): ?string
    {
        $this->change_tab();
        $image_subform = $this->get_first_image_subform();
        $variant_field = $image_subform->find('css', '[data-test-product-variant]');
        $selected_option = $variant_field->find('css', 'option[selected]');
        if (null === $selected_option) {
            return null;
        }
        return $selected_option->get_text();
    }
    public function count_images(): int
    {
        $images = $this->get_element('images');
        $image_subforms = $images->find_all('css', '[data-test-image-subform]');
        return count($image_subforms);
    }
    public function get_images(): array
    {
        $images = $this->get_element('images');
        return $images->find_all('css', '[data-test-image-subform]');
    }
    public function assert_image_type_and_position($image, string $expected_type, int $expected_position): void
    {
        $type = $image->find('css', 'input[data-test-type]')->get_value();
        $position = $image->find('css', 'input[data-test-position]')->get_value();
        if (!$type || !$position) {
            throw new \Exception('Type or position element not found in the image subform.');
        }
        if ($type !== $expected_type) {
            throw new \Exception(sprintf('Expected type "%s", but got "%s".', $expected_type, $type));
        }
        if ((int) $position !== $expected_position) {
            throw new \Exception(sprintf('Expected position "%d", but got "%d".', $expected_position, $position));
        }
    }
    public function modify_first_image_type(string $type): void
    {
        $this->change_tab();
        $first_image_subform = $this->get_first_image_subform();
        $first_image_subform->find('css', 'input[data-test-type]')->set_value($type);
    }
    public function modify_first_image_position(int $position): void
    {
        $this->change_tab();
        $first_image_subform = $this->get_first_image_subform();
        $first_image_subform->find('css', 'input[data-test-position]')->set_value($position);
    }
    public function modify_position_of_image_with_type(string $type, int $position): void
    {
        $this->change_tab();
        $image_subform = $this->get_element('image_subform_with_type', ['%type%' => $type]);
        $image_subform->find('css', 'input[data-test-position]')->set_value($position);
    }
    public function has_image_with_type_on_position(string $type, int $position): bool
    {
        $this->change_tab();
        $image_subform = $this->get_element('image_subform_with_type', ['%type%' => $type]);
        $image_position = $image_subform->find('css', 'input[data-test-position]')->get_value();
        return $image_position === (string) $position;
    }
    public function select_variant_for_first_image(Product_Variant_Interface $product_variant): void
    {
        $this->change_tab();
        $image_subform = $this->get_first_image_subform();
        $this->autocomplete_helper->select_by_name($this->get_driver(), $image_subform->find('css', '[data-test-product-variant]')->get_xpath(), $product_variant->get_name());
    }
    public function has_validation_error_with_message(string $message): bool
    {
        $validation_message = $this->get_document()->find('css', '.invalid-feedback');
        return $validation_message !== null && $validation_message->get_text() === $message;
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['add_image' => '[data-test-add-image]', 'image_subform_with_type' => '[data-test-image-subform][data-test-type="%type%"]', 'images' => '[data-test-images]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]']);
    }
    protected function get_first_image_subform(): Node_Element
    {
        $images = $this->get_element('images');
        $image_subforms = $images->find_all('css', '[data-test-image-subform]');
        return reset($image_subforms);
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'media'])->click();
    }
}