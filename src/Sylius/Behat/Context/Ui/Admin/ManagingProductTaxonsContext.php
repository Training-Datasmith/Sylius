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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Element\Admin\Product\Taxonomy_Form_Element_Interface;
use Sylius\Behat\Page\Admin\Product\Update_Simple_Product_Page_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Managing_Product_Taxons_Context implements Context
{
    public function __construct(private Update_Simple_Product_Page_Interface $update_simple_product_page, private Taxonomy_Form_Element_Interface $taxonomy_form_element)
    {
    }
    #[When('I add :taxon taxon to the :product product')]
    #[When('I assign the :taxon taxon to the :product product')]
    public function i_add_taxon_to_the_product(Product_Interface $product, Taxon_Interface $taxon): void
    {
        $this->taxonomy_form_element->check_product_taxon($taxon);
    }
    #[When('I change that the :product product does not belong to the :taxon taxon')]
    public function i_change_that_the_product_does_not_belong_to_the_taxon(Product_Interface $product, Taxon_Interface $taxon): void
    {
        if (!$this->update_simple_product_page->is_open(['id' => $product->get_id()])) {
            $this->update_simple_product_page->open(['id' => $product->get_id()]);
        }
        $this->taxonomy_form_element->uncheck_product_taxon($taxon);
    }
    #[When('I check all taxons')]
    public function i_check_all_taxons(): void
    {
        $this->taxonomy_form_element->check_all_taxons();
    }
    #[When('I uncheck all taxons')]
    public function i_uncheck_all_taxons(): void
    {
        $this->taxonomy_form_element->uncheck_all_taxons();
    }
    #[When('I filter taxons by :phrase')]
    public function i_filter_taxons_by(string $phrase): void
    {
        $this->taxonomy_form_element->filter_taxons_by($phrase);
    }
    #[Then('the product :product should have the :taxon taxon')]
    public function this_product_taxon_should_have_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::true($this->taxonomy_form_element->is_taxon_chosen($taxon->get_code()));
    }
    #[Then('the product :product should not have the :taxon taxon')]
    public function this_product_taxon_should_not_have_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::false($this->taxonomy_form_element->is_taxon_chosen($taxon->get_code()));
    }
    #[Then('I should see the :taxon taxon')]
    public function i_should_see_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::true($this->taxonomy_form_element->has_taxon($taxon->get_code()));
    }
    #[Then('I should not see the :taxon taxon')]
    public function i_should_not_see_the_taxon(Taxon_Interface $taxon): void
    {
        Assert::false($this->taxonomy_form_element->has_taxon($taxon->get_code()));
    }
}