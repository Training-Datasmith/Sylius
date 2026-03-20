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
namespace Sylius\Bundle\Admin_Bundle\Form\Extension\Promotion;

use Sylius\Bundle\Promotion_Bundle\Form\Type\Promotion_Action_Type;
use Symfony\Component\Form\Abstract_Type_Extension;
use Symfony\Component\Form\Extension\Core\Type\Hidden_Type;
use Symfony\Component\Form\Form_Builder_Interface;
final class Promotion_Action_Type_Extension extends Abstract_Type_Extension
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('type', Hidden_Type::class);
    }
    public static function get_extended_types(): iterable
    {
        yield Promotion_Action_Type::class;
    }
}