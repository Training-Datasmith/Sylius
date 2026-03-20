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
namespace Sylius\Bundle\Admin_Bundle\Controller;

use Sylius\Component\Core\Customer\Statistics\Customer_Statistics_Provider_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Twig\Environment;
final readonly class Customer_Statistics_Controller
{
    /** @param RepositoryInterface<CustomerInterface> $customerRepository */
    public function __construct(private Customer_Statistics_Provider_Interface $statistics_provider, private Repository_Interface $customer_repository, private Environment $templating_engine)
    {
    }
    /**
     * @throws HttpException
     */
    public function render_action(Request $request): Response
    {
        $customer_id = $request->query->get('customerId');
        /** @var CustomerInterface|null $customer */
        $customer = $this->customer_repository->find($customer_id);
        if (null === $customer) {
            throw new Http_Exception(Response::HTTP_BAD_REQUEST, sprintf('Customer with id %s doesn\'t exist.', (string) $customer_id));
        }
        $customer_statistics = $this->statistics_provider->get_customer_statistics($customer);
        return new Response($this->templating_engine->render('@SyliusAdmin/Customer/Show/Statistics/index.html.twig', ['statistics' => $customer_statistics]));
    }
}