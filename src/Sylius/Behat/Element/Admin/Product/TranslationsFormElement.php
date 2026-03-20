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

use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
class Translations_Form_Element extends Base_Form_Element implements Translations_Form_Element_Interface
{
    public function name_it_in(string $name, string $locale_code): void
    {
        $this->change_tab();
        $this->expand_translation_accordion($locale_code);
        $this->get_element('name', ['%locale_code%' => $locale_code])->set_value($name);
    }
    public function has_name_in_locale(string $name, string $locale_code): bool
    {
        return $this->get_element('name', ['%locale_code%' => $locale_code])->get_value() === $name;
    }
    public function generate_slug(string $locale_code): void
    {
        $this->get_element('generate_product_slug_button', ['%locale_code%' => $locale_code])->click();
        $this->wait_for_form_update();
    }
    public function get_slug(string $locale): string
    {
        return $this->get_element('slug', ['%locale_code%' => $locale])->get_value();
    }
    public function specify_slug_in(string $slug, string $locale): void
    {
        $this->change_tab();
        $this->get_element('slug', ['%locale_code%' => $locale])->set_value($slug);
    }
    public function set_meta_keywords(string $keywords, string $locale_code): void
    {
        $this->get_element('meta_keywords', ['%locale_code%' => $locale_code])->set_value($keywords);
    }
    public function set_meta_description(string $description, string $locale_code): void
    {
        $this->get_element('meta_description', ['%locale_code%' => $locale_code])->set_value($description);
    }
    public function activate_language_tab(string $locale_code): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $language_tab_title = $this->get_element('language_tab', ['%locale_code%' => $locale_code]);
        if (!$language_tab_title->has_class('active')) {
            $language_tab_title->click();
        }
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['generate_product_slug_button' => '[data-test-generate-product-slug-button="%locale_code%"]', 'meta_description' => '[data-test-meta-description="%locale_code%"]', 'meta_keywords' => '[data-test-meta-keywords="%locale_code%"]', 'name' => '[data-test-name="%locale_code%"]', 'product_translation_accordion' => '[data-test-product-translations-accordion="%locale_code%"]', 'side_navigation_tab' => '[data-test-side-navigation-tab="%name%"]', 'slug' => '[data-test-slug="%locale_code%"]']);
    }
    protected function expand_translation_accordion(string $locale_code): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $translation_accordion = $this->get_element('product_translation_accordion', ['%locale_code%' => $locale_code]);
        if ($translation_accordion->get_attribute('aria-expanded') === 'true') {
            return;
        }
        $translation_accordion->click();
    }
    protected function change_tab(): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $this->get_element('side_navigation_tab', ['%name%' => 'translations'])->click();
    }
}