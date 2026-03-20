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

use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Button_Type;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Form\Form_View;
use Symfony\Component\Options_Resolver\Options_Resolver;
final class Add_Button_Type extends Abstract_Type
{
    public const OPTION_TYPES = 'types';
    public function build_view(Form_View $view, Form_Interface $form, array $options): void
    {
        $view->vars[self::OPTION_TYPES] = $options[self::OPTION_TYPES];
    }
    public function configure_options(Options_Resolver $resolver): void
    {
        $resolver->set_default(self::OPTION_TYPES, [])->set_allowed_types(self::OPTION_TYPES, 'array');
    }
    public function get_parent(): string
    {
        return Button_Type::class;
    }
}