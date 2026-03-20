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
namespace Sylius\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Doctrine\Persistence\Object_Manager;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Image_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Core\Uploader\Image_Uploader_Interface;
use Sylius\Component\Taxonomy\Generator\Taxon_Slug_Generator_Interface;
use Sylius\Component\Taxonomy\Model\Taxon_Translation_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
final class Taxonomy_Context implements Context
{
    public function __construct(private readonly Repository_Interface $taxon_repository, private readonly Factory_Interface $taxon_factory, private readonly Factory_Interface $taxon_translation_factory, private readonly Factory_Interface $taxon_image_factory, private readonly Object_Manager $object_manager, private readonly Image_Uploader_Interface $image_uploader, private readonly Taxon_Slug_Generator_Interface $taxon_slug_generator, private \ArrayAccess $mink_parameters)
    {
    }
    #[Given('the store has :firstTaxonName taxonomy')]
    #[Given('the store classifies its products as :firstTaxonName')]
    #[Given('the store classifies its products as :firstTaxonName and :secondTaxonName')]
    #[Given('the store classifies its products as :firstTaxonName, :secondTaxonName and :thirdTaxonName')]
    #[Given('the store classifies its products as :firstTaxonName, :secondTaxonName, :thirdTaxonName and :fourthTaxonName')]
    public function store_classifies_its_products_as(...$taxons_names): void
    {
        foreach ($taxons_names as $taxon_name) {
            $this->taxon_repository->add($this->create_taxon($taxon_name));
        }
    }
    #[Given('the store classifies its products as :taxonName with :taxonCode code')]
    public function store_classifies_its_products_as_with_code(string $taxon_name, string $taxon_code): void
    {
        $this->taxon_repository->add($this->create_taxon_with_code($taxon_name, $taxon_code));
    }
    #[Given('/^the store has taxonomy named "([^"]+)" in ("[^"]+" locale) and "([^"]+)" in ("[^"]+" locale)$/')]
    public function the_store_has_taxonomy_named_in_and_in($first_name, $first_locale, $second_name, $second_locale): void
    {
        $translation_map = [$first_locale => $first_name, $second_locale => $second_name];
        $this->taxon_repository->add($this->create_taxon_in_many_languages($translation_map));
    }
    #[Given('/^the ("[^"]+" taxon) has child taxon "([^"]+)" in many locales$/')]
    public function the_taxon_has_children_taxons_in_many_locales(Taxon_Interface $taxon, string $child_taxon_name): void
    {
        $translation_map = ['en_US' => $child_taxon_name, 'fr_FR' => $child_taxon_name . '_FR', 'de_DE' => $child_taxon_name . '_DE', 'es_ES' => $child_taxon_name . '_ES', 'pl_PL' => $child_taxon_name . '_PL', 'pt_PT' => $child_taxon_name . '_PT', 'uk_UA' => $child_taxon_name . '_UA', 'cn_CN' => $child_taxon_name . '_CN', 'ja_JP' => $child_taxon_name . '_JP', 'bg_BG' => $child_taxon_name . '_BG', 'da_DK' => $child_taxon_name . '_DK'];
        $taxon->add_child($this->create_taxon_in_many_languages($translation_map));
        $this->object_manager->persist($taxon);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" taxon)(?:| also) has an image "([^"]+)" with "([^"]+)" type$/')]
    public function the_taxon_has_an_image_with_type(Taxon_Interface $taxon, string $image_path, ?string $image_type): void
    {
        $files_path = $this->get_parameter('files_path');
        /** @var ImageInterface $taxonImage */
        $taxon_image = $this->taxon_image_factory->create_new();
        $taxon_image->set_file(new Uploaded_File($files_path . $image_path, basename($image_path)));
        $taxon_image->set_type($image_type);
        $this->image_uploader->upload($taxon_image);
        $taxon->add_image($taxon_image);
        $this->object_manager->persist($taxon);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" taxon) has child taxon "([^"]+)"$/')]
    #[Given('/^the ("[^"]+" taxon) has children taxon "([^"]+)" and "([^"]+)"$/')]
    #[Given('/^the ("[^"]+" taxon) has children taxons "([^"]+)" and "([^"]+)"$/')]
    #[Given('/^the ("[^"]+" taxon) has children taxons "([^"]+)", "([^"]+)" and "([^"]+)"$/')]
    public function the_taxon_has_children_taxon_and(Taxon_Interface $taxon, string ...$taxons_names): void
    {
        foreach ($taxons_names as $taxon_name) {
            $taxon->add_child($this->create_child_taxon($taxon_name, $taxon));
        }
        $this->object_manager->persist($taxon);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" taxon)(?:| also) is enabled/')]
    public function the_taxon_is_enabled(Taxon_Interface $taxon): void
    {
        $taxon->set_enabled(true);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" taxon)(?:| also) is disabled$/')]
    public function the_taxon_is_disabled(Taxon_Interface $taxon): void
    {
        $taxon->set_enabled(false);
        $this->object_manager->flush();
    }
    #[Given('/^the ("[^"]+" taxon) has an empty name in the ("[^"]+" locale)$/')]
    public function the_taxon_has_empty_name_in_locale(Taxon_Interface $taxon, string $locale_code): void
    {
        $taxon->get_translation($locale_code)->set_name('');
        $this->object_manager->flush();
    }
    private function create_taxon(string $name): Taxon_Interface
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->taxon_factory->create_new();
        $taxon->set_name($name);
        $taxon->set_code(String_Inflector::name_to_lowercase_code($name));
        $taxon->set_slug($this->taxon_slug_generator->generate($taxon));
        return $taxon;
    }
    private function create_taxon_with_code(string $name, string $code): Taxon_Interface
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->taxon_factory->create_new();
        $taxon->set_name($name);
        $taxon->set_code($code);
        $taxon->set_slug($this->taxon_slug_generator->generate($taxon));
        return $taxon;
    }
    private function create_child_taxon(string $name, Taxon_Interface $parent): Taxon_Interface
    {
        $child = $this->create_taxon($name);
        $child->set_parent($parent);
        $child->set_slug($this->taxon_slug_generator->generate($child));
        return $child;
    }
    private function create_taxon_in_many_languages(array $names): Taxon_Interface
    {
        /** @var TaxonInterface $taxon */
        $taxon = $this->taxon_factory->create_new();
        $taxon->set_code(String_Inflector::name_to_code($names['en_US']));
        foreach ($names as $locale => $name) {
            /** @var TaxonTranslationInterface $taxonTranslation */
            $taxon_translation = $this->taxon_translation_factory->create_new();
            $taxon_translation->set_locale($locale);
            $taxon_translation->set_name($name);
            $taxon->add_translation($taxon_translation);
            $taxon_translation->set_slug($this->taxon_slug_generator->generate($taxon, $locale));
        }
        return $taxon;
    }
    private function get_parameter(string $name): ?string
    {
        return $this->mink_parameters[$name] ?? null;
    }
}