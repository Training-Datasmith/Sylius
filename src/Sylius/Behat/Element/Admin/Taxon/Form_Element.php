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

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Session;
use Friends_Of_Behat\Symfony_Extension\Mink\Mink_Parameters;
use Sylius\Behat\Behaviour\Checks_Code_Immutability;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Element\Admin\Crud\Form_Element as BaseFormElement;
use Sylius\Behat\Service\Driver_Helper;
use Sylius\Behat\Service\Helper\Autocomplete_Helper_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
class Form_Element extends Base_Form_Element implements Form_Element_Interface
{
    use Checks_Code_Immutability;
    use Specifies_Its_Field;
    public function __construct(Session $session, array|Mink_Parameters $mink_parameters, protected readonly Autocomplete_Helper_Interface $autocomplete_helper)
    {
    }
    public function get_code(): string
    {
        return $this->get_element('code')->get_value();
    }
    public function name_it(string $name, string $locale_code): void
    {
        $this->expand_translation_accordion($locale_code);
        $this->get_element('name', ['%locale_code%' => $locale_code])->set_value($name);
    }
    public function slug_it(string $slug, string $locale_code): void
    {
        $this->get_element('slug', ['%locale_code%' => $locale_code])->set_value($slug);
    }
    public function generate_slug(string $locale_code): void
    {
        $this->get_element('generate_slug_button', ['%locale_code%' => $locale_code])->click();
        $this->wait_for_form_update();
    }
    public function describe_it_as(string $description, string $locale_code): void
    {
        $this->get_element('description', ['%locale_code%' => $locale_code])->set_value($description);
    }
    public function get_parent(): string
    {
        $parent_element = $this->get_element('parent');
        if (Driver_Helper::is_javascript($this->get_driver())) {
            $items = $this->autocomplete_helper->get_selected_items($this->get_driver(), $parent_element->get_xpath());
            return count($items) > 0 ? reset($items) : '';
        }
        $selected_option = $parent_element->find('css', 'option[selected]');
        return $selected_option?->get_text() ?? '';
    }
    public function choose_parent(Taxon_Interface $taxon): void
    {
        $this->autocomplete_helper->select_by_name($this->get_driver(), $this->get_element('parent')->get_xpath(), $taxon->get_name());
        $this->wait_for_form_update();
    }
    public function remove_current_parent(): void
    {
        $this->autocomplete_helper->clear($this->get_driver(), $this->get_element('parent')->get_xpath());
        $this->wait_for_form_update();
    }
    public function get_translation_field_value(string $element, string $locale_code): string
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        return $this->get_element($element, ['%locale_code%' => $locale_code])->get_value();
    }
    public function enable(): void
    {
        $this->get_element('enabled')->check();
    }
    public function disable(): void
    {
        $this->get_element('enabled')->uncheck();
    }
    public function is_enabled(): bool
    {
        return $this->get_element('enabled')->is_checked();
    }
    protected function get_defined_elements(): array
    {
        return array_merge(parent::get_defined_elements(), ['code' => '[data-test-code]', 'description' => '[data-test-description="%locale_code%"]', 'enabled' => '[data-test-enabled]', 'form' => '[data-live-name-value="sylius_admin:taxon:form"]', 'generate_slug_button' => '[data-test-generate-slug-button="%locale_code%"]', 'name' => '[data-test-name="%locale_code%"]', 'parent' => '[data-test-parent]', 'slug' => '[data-test-slug="%locale_code%"]', 'translation_accordion' => '[data-test-taxon-translations-accordion="%locale_code%"]']);
    }
    protected function get_code_element(): Node_Element
    {
        return $this->get_element('code');
    }
    protected function expand_translation_accordion(string $locale_code): void
    {
        if (Driver_Helper::is_not_javascript($this->get_driver())) {
            return;
        }
        $translation_accordion = $this->get_element('translation_accordion', ['%locale_code%' => $locale_code]);
        if ($translation_accordion->get_attribute('aria-expanded') === 'true') {
            return;
        }
        $translation_accordion->click();
    }
    public function search_parent_taxon(string $search_term): array
    {
        Driver_Helper::wait_for_page_to_load($this->get_session());
        $search_results = $this->autocomplete_helper->search($this->get_driver(), $this->get_element('parent')->get_xpath(), $search_term);
        return is_array($search_results) ? array_values($search_results) : [];
    }
}