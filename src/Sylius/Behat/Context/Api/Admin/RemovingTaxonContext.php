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
use Sylius\Component\Core\Model\Taxon_Interface;
use Webmozart\Assert\Assert;
final readonly class Removing_Taxon_Context implements Context
{
    public function __construct(private Api_Client_Interface $client, private Response_Checker_Interface $response_checker)
    {
    }
    #[When('I (try to) delete taxon named :taxon')]
    public function i_delete_taxon(Taxon_Interface $taxon): void
    {
        $this->client->delete(Resources::TAXONS, $taxon->get_code());
    }
    #[Then('the :taxon taxon should still exist')]
    public function the_taxon_should_still_exist(Taxon_Interface $taxon): void
    {
        $this->client->show(Resources::TAXONS, $taxon->get_code());
        Assert::true($this->response_checker->is_show_successful($this->client->get_last_response()));
    }
    #[Then('I should be notified that this taxon could not be deleted as it is in use by a promotion rule')]
    public function i_should_be_notified_that_this_taxon_could_not_be_deleted(): void
    {
        Assert::contains($this->response_checker->get_error($this->client->get_last_response()), 'Cannot delete a taxon that is in use by a promotion rule.');
    }
}