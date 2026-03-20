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
namespace Sylius\Bundle\Admin_Bundle\Form\Type;

use Sylius\Bundle\Currency_Bundle\Form\Type\Currency_Type as BaseCurrencyType;
use Sylius\Component\Currency\Model\Currency_Interface;
use Sylius\Component\Currency\Repository\Currency_Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Currency_Type as SymfonyCurrencyType;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
use Symfony\Component\Intl\Currencies;
final class Currency_Type extends Abstract_Type
{
    /** @param CurrencyRepositoryInterface<CurrencyInterface> $currencyRepository */
    public function __construct(private readonly Currency_Repository_Interface $currency_repository)
    {
    }
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add_event_listener(Form_Events::PRE_SET_DATA, function (Form_Event $event): void {
            $options = ['label' => 'sylius.form.currency.code', 'choice_loader' => null, 'autocomplete' => true, 'placeholder' => 'sylius.form.currency.select'];
            $currency = $event->get_data();
            if ($currency instanceof Currency_Interface && null !== $currency->get_code()) {
                $options['disabled'] = true;
                $options['choices'] = [Currencies::get_name($currency->get_code()) => $currency->get_code()];
            } else {
                $options['choices'] = array_flip($this->get_available_currencies());
            }
            $form = $event->get_form();
            $form->add('code', Symfony_Currency_Type::class, $options);
        });
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_currency';
    }
    public function get_parent(): string
    {
        return Base_Currency_Type::class;
    }
    /**
     * @return array<string, string>
     */
    private function get_available_currencies(): array
    {
        $available_currencies = Currencies::get_names();
        /** @var CurrencyInterface[] $definedCurrencies */
        $defined_currencies = $this->currency_repository->find_all();
        foreach ($defined_currencies as $currency) {
            unset($available_currencies[$currency->get_code()]);
        }
        return $available_currencies;
    }
}