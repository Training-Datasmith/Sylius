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
namespace Sylius\Bundle\Addressing_Bundle\Form\Type;

use Sylius\Component\Addressing\Model\Country_Interface;
use Sylius\Component\Addressing\Model\Province_Interface;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Choice_Type;
use Symfony\Component\Options_Resolver\Options;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Province_Choice_Type extends Abstract_Type
{
    /** @param RepositoryInterface<ProvinceInterface> $provinceRepository */
    public function __construct(private readonly Repository_Interface $province_repository)
    {
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_defaults(['choices' => function (Options $options): iterable {
            if (null === $options['country']) {
                return $this->province_repository->find_all();
            }
            return $options['country']->get_provinces();
        }, 'choice_value' => 'code', 'choice_label' => 'name', 'choice_translation_domain' => false, 'country' => null, 'label' => 'sylius.form.address.province', 'placeholder' => 'sylius.form.province.select']);
        $resolver->add_allowed_types('country', ['null', Country_Interface::class]);
    }
    public function get_parent(): string
    {
        return Choice_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_province_choice';
    }
}