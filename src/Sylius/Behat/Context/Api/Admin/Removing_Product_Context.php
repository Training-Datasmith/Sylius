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
namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Client\Api_Client_Interface;
use Sylius\Behat\Client\Response_Checker_Interface;
use Sylius\Behat\Context\Api\Resources;
use Sylius\Component\Core\Model\Product_Interface;
use Webmozart\Assert\Assert;
final readonly class Removing_Product_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I (try to) delete the :product product')]
    public function i_delete_product(Product_Interface $product): void
    {
        $this->client->delete(Resources::PRODUCTS, $product->get_code());
    }
    #[Then('/^(this product) should still exist$/')]
    public function the_product_should_still_exist(Product_Interface $product): void
    {
        $this->client->show(Resources::PRODUCTS, $product->get_code());
        Assert::true($this->response_checker->is_show_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that this product could not be deleted as it is in use by a promotion rule')]
    public function i_should_be_notified_that_this_product_could_not_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete a product that is in use by a promotion rule.');
    }
}