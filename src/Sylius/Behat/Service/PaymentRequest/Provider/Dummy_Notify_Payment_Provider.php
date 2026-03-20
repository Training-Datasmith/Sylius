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
namespace Sylius\Behat\Service\Payment_Request\Provider;

use Sylius\Bundle\Payment_Bundle\Attribute\As_Notify_Payment_Provider;
use Sylius\Bundle\Payment_Bundle\Provider\Notify_Payment_Provider_Interface;
use Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Entity_Repository;
use Sylius\Component\Payment\Model\Payment_Interface;
use Sylius\Component\Payment\Model\Payment_Method_Interface;
use Symfony\Component\Http_Foundation\Request;
#[As_Notify_Payment_Provider]
final readonly class Dummy_Notify_Payment_Provider implements Notify_Payment_Provider_Interface
{
    public function __construct(private Entity_Repository $payment_repository)
    {
    }
    public function get_payment(Request $request, Payment_Method_Interface $payment_method): Payment_Interface
    {
        /** @var PaymentInterface[] $payments */
        $payments = $this->payment_repository->find_by([], ['createdAt' => 'ASC'], 1);
        return $payments[0];
    }
    public function supports(Request $request, Payment_Method_Interface $payment_method): bool
    {
        return true;
    }
}