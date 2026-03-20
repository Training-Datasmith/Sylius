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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Review\Model\Review_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Product_Review_Context implements Context
{
    public function __construct(private Repository_Interface $product_review_repository)
    {
    }
    #[Transform(':productReview')]
    #[Transform('/^"([^"]+)" product review$/')]
    public function get_product_review_by_title(string $title): Review_Interface
    {
        $product_review = $this->product_review_repository->find_one_by(['title' => $title]);
        Assert::not_null($product_review, sprintf('Product review with title "%s" does not exist', $title));
        return $product_review;
    }
}