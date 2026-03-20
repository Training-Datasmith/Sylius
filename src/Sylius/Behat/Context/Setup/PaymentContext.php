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
use Behat\Step\When;
use Doctrine\Persistence\Object_Manager;
use Sylius\Behat\Service\Shared_Storage_Interface;
use Sylius\Bundle\Core_Bundle\Fixture\Factory\Example_Factory_Interface;
use Sylius\Bundle\Payum_Bundle\Model\Gateway_Config_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Sylius\Component\Core\Model\Channel_Interface;
use Sylius\Component\Core\Model\Payment_Method_Interface;
use Sylius\Component\Payment\Model\Payment_Method_Translation_Interface;
use Sylius\Component\Payment\Repository\Payment_Method_Repository_Interface;
use Sylius\Resource\Factory\Factory_Interface;
final readonly class Payment_Context implements Context
{
    /**
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param ExampleFactoryInterface<PaymentMethodInterface> $paymentMethodExampleFactory
     * @param FactoryInterface<PaymentMethodTranslationInterface> $paymentMethodTranslationFactory
     * @param array<string, string> $gatewayFactories
     */
    public function __construct(private Shared_Storage_Interface $shared_storage, private Payment_Method_Repository_Interface $payment_method_repository, private Example_Factory_Interface $payment_method_example_factory, private Factory_Interface $payment_method_translation_factory, private Object_Manager $payment_method_manager, private array $gateway_factories)
    {
    }
    #[Given('the store (also )allows paying (with ):paymentMethodName')]
    #[Given('the store (also )allows paying (with ):paymentMethodName at position :position')]
    public function store_allows_paying(string $payment_method_name, ?int $position = null): void
    {
        $this->create_payment_method($payment_method_name, String_Inflector::name_to_code($payment_method_name), 'Offline', 'Payment method', true, $position);
    }
    #[Given('the store has disabled all payment methods')]
    public function the_store_has_disabled_all_payment_methods(): void
    {
        $payment_methods = $this->payment_method_repository->find_all();
        /** @var PaymentMethodInterface $paymentMethod */
        foreach ($payment_methods as $payment_method) {
            $payment_method->set_enabled(false);
        }
        $this->payment_method_manager->flush();
    }
    #[Given('/^the store allows paying (\w+) for (all channels)$/')]
    public function store_allows_paying_for_all_channels(string $payment_method_name, array $channels): void
    {
        $payment_method = $this->create_payment_method($payment_method_name, String_Inflector::name_to_uppercase_code($payment_method_name), 'Offline', 'Payment method', false);
        foreach ($channels as $channel) {
            $payment_method->add_channel($channel);
        }
    }
    #[Given('the store has (also) a payment method :paymentMethodName with a code :paymentMethodCode')]
    public function the_store_has_a_payment_method_with_a_code(string $payment_method_name, string $payment_method_code): void
    {
        $this->create_payment_method($payment_method_name, $payment_method_code, 'Offline');
    }
    #[Given('/^(this payment method) is named "([^"]+)" in the "([^"]+)" locale$/')]
    public function this_payment_method_is_named_in(Payment_Method_Interface $payment_method, ?string $name, $locale): void
    {
        /** @var PaymentMethodTranslationInterface $translation */
        $translation = $this->payment_method_translation_factory->create_new();
        $translation->set_locale($locale);
        $translation->set_name($name);
        $payment_method->add_translation($translation);
        $this->payment_method_manager->flush();
    }
    #[Given('/^(this payment method) is not using Payum$/')]
    public function this_payment_method_is_not_using_payum(Payment_Method_Interface $payment_method): void
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gateway_config = $payment_method->get_gateway_config();
        $gateway_config->set_use_payum(false);
        $this->payment_method_manager->flush();
    }
    #[Given('the payment method :paymentMethod is disabled')]
    #[Given('/^(this payment method) (?:has been|is) disabled$/')]
    #[When('the payment method :paymentMethod gets disabled')]
    public function the_store_has_a_payment_method_disabled(Payment_Method_Interface $payment_method): void
    {
        $payment_method->disable();
        $this->payment_method_manager->flush();
    }
    #[Given('/^(it) has instructions "([^"]+)"$/')]
    public function it_has_instructions(Payment_Method_Interface $payment_method, ?string $instructions): void
    {
        $payment_method->set_instructions($instructions);
        $this->payment_method_manager->flush();
    }
    #[Given('the store has :paymentMethodName payment method not assigned to any channel')]
    public function the_store_has_payment_method_not_assigned_to_any_channel(string $payment_method_name): void
    {
        $this->create_payment_method($payment_method_name, 'PM_' . $payment_method_name, 'Offline', 'Payment method', false);
    }
    #[Given('the payment method :paymentMethod requires authorization before capturing')]
    public function the_payment_method_requires_authorization_before_capturing(Payment_Method_Interface $payment_method): void
    {
        /** @var GatewayConfigInterface $config */
        $config = $payment_method->get_gateway_config();
        $config->set_config(array_merge($config->get_config(), ['use_authorize' => true]));
        $payment_method->set_gateway_config($config);
        $this->payment_method_manager->flush();
    }
    #[Given('the store allows paying with :paymentMethodName in :channel channel')]
    public function the_store_allows_paying_with_in_channel(string $payment_method_name, Channel_Interface $channel): void
    {
        $payment_method = $this->create_payment_method($payment_method_name, String_Inflector::name_to_uppercase_code($payment_method_name), 'Offline', 'Payment method', false);
        $payment_method->add_channel($channel);
    }
    private function create_payment_method(string $name, string $code, string $gateway_factory, string $description = '', bool $add_for_current_channel = true, ?int $position = null): Payment_Method_Interface
    {
        $gateway_factory = array_search($gateway_factory, $this->gateway_factories);
        /** @var PaymentMethodInterface $paymentMethod */
        $payment_method = $this->payment_method_example_factory->create(['name' => ucfirst($name), 'code' => $code, 'description' => $description, 'gatewayName' => $gateway_factory, 'gatewayFactory' => $gateway_factory, 'enabled' => true, 'channels' => $add_for_current_channel && $this->shared_storage->has('channel') ? [$this->shared_storage->get('channel')] : []]);
        if (null !== $position) {
            $payment_method->set_position($position);
        }
        $this->shared_storage->set('payment_method', $payment_method);
        $this->payment_method_repository->add($payment_method);
        return $payment_method;
    }
}