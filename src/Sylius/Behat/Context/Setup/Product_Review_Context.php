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
use Sylius\Abstraction\State_Machine\State_Machine_Interface;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Component\Core\Model\Customer_Interface;
use Sylius\Component\Core\Model\Product_Interface;
use Sylius\Component\Core\Product_Review_Transitions;
use Sylius\Component\Review\Model\Review_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Product_Review_Context implements Context
{
    public function __construct(private Shared_Storage_Interface $shared_storage, private Factory_Interface $product_review_factory, private Repository_Interface $product_review_repository, private State_Machine_Interface $state_machine)
    {
    }
    #[Given('/^(this product) has one review from (customer "[^"]+")$/')]
    public function product_has_a_review(Product_Interface $product, Customer_Interface $customer): void
    {
        $review = $this->create_product_review($product, 'Title', 5, 'Comment', $customer);
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product) has(?:| also) a review titled "([^"]+)" and rated (\d+) added by (customer "[^"]+")(?:|, created (\d+) days ago)$/')]
    #[Given('/^(this product) has(?:| also) an accepted review titled "([^"]+)" and rated (\d+) added by (customer "[^"]+")(?:|, created (\d+) days ago)$/')]
    public function this_product_has_an_accepted_review_titled_and_rated_added_by_customer(Product_Interface $product, string $title, int $rating, Customer_Interface $customer, ?int $days_since_creation = null): void
    {
        $review = $this->create_product_review($product, $title, $rating, $title, $customer);
        if (null !== $days_since_creation) {
            $review->set_created_at(new \DateTime('-' . $days_since_creation . ' days'));
        }
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product) has(?:| also) a rejected review titled "([^"]+)" and rated (\d+) added by (customer "[^"]+")(?:|, created (\d+) days ago)$/')]
    public function this_product_has_a_rejected_review_titled_and_rated_added_by_customer(Product_Interface $product, string $title, int $rating, Customer_Interface $customer, ?int $days_since_creation = null): void
    {
        $review = $this->create_product_review($product, $title, $rating, $title, $customer, Product_Review_Transitions::TRANSITION_REJECT);
        if (null !== $days_since_creation) {
            $review->set_created_at(new \DateTime('-' . $days_since_creation . ' days'));
        }
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product) has(?:| also) a new review titled "([^"]+)" and rated (\d+) added by (customer "[^"]+")(?:|, created (\d+) days ago)$/')]
    public function this_product_has_a_new_review_titled_and_rated_added_by_customer(Product_Interface $product, string $title, int $rating, Customer_Interface $customer, ?int $days_since_creation = null): void
    {
        $review = $this->create_product_review($product, $title, $rating, $title, $customer, null);
        if (null !== $days_since_creation) {
            $review->set_created_at(new \DateTime('-' . $days_since_creation . ' days'));
        }
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product) has(?:| also) a review titled "([^"]+)" and rated (\d+) with a comment "([^"]+)" added by (customer "[^"]+")$/')]
    public function this_product_has_a_review_titled_and_rated_with_a_comment_added_by_customer(Product_Interface $product, string $title, int $rating, string $comment, Customer_Interface $customer): void
    {
        $review = $this->create_product_review($product, $title, $rating, $comment, $customer);
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product)(?:| also) has accepted reviews rated (\d+), (\d+), (\d+), (\d+) and (\d+)$/')]
    #[Given('/^(this product)(?:| also) has accepted reviews rated (\d+), (\d+) and (\d+)$/')]
    public function this_product_has_accepted_reviews_rated(Product_Interface $product, int ...$rates): void
    {
        $customer = $this->shared_storage->get('customer');
        foreach ($rates as $key => $rate) {
            $review = $this->create_product_review($product, 'Title ' . $key, $rate, 'Comment ' . $key, $customer);
            $this->product_review_repository->add($review);
        }
    }
    #[Given('/^(this product)(?:| also) has review rated (\d+) which is not accepted yet$/')]
    public function it_also_has_review_rated_which_is_not_accepted_yet(Product_Interface $product, int $rate): void
    {
        $customer = $this->shared_storage->get('customer');
        $review = $this->create_product_review($product, 'Title', $rate, 'Comment', $customer, null);
        $this->product_review_repository->add($review);
    }
    #[Given('/^(this product) also has review rated (\d+) which is rejected$/')]
    public function it_also_has_review_rated_which_is_rejected(Product_Interface $product, int $rate): void
    {
        $customer = $this->shared_storage->get('customer');
        $review = $this->create_product_review($product, 'Title', $rate, 'Comment', $customer, Product_Review_Transitions::TRANSITION_REJECT);
        $this->product_review_repository->add($review);
    }
    /**
     * @return ReviewInterface
     */
    private function create_product_review(Product_Interface $product, string $title, int $rating, string $comment, ?Customer_Interface $customer = null, ?string $transition = Product_Review_Transitions::TRANSITION_ACCEPT)
    {
        /** @var ReviewInterface $review */
        $review = $this->product_review_factory->create_new();
        $review->set_title($title);
        $review->set_rating($rating);
        $review->set_comment($comment);
        $review->set_review_subject($product);
        $review->set_author($customer);
        $product->add_review($review);
        if (null !== $transition) {
            $this->state_machine->apply($review, Product_Review_Transitions::GRAPH, $transition);
        }
        $this->shared_storage->set('product_review', $review);
        return $review;
    }
}