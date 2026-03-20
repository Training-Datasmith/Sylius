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

use Sylius\Bundle\Channel_Bundle\Form\Type\Channel_Type as BaseChannelType;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Form_Builder_Interface;
final class Channel_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('menuTaxon', Taxon_Autocomplete_Type::class, ['label' => 'sylius.form.channel.menu_taxon', 'multiple' => false])->add('channelPriceHistoryConfig', Channel_Price_History_Config_Type::class, ['label' => false, 'required' => false]);
    }
    public function get_parent(): string
    {
        return Base_Channel_Type::class;
    }
    public function get_block_prefix(): string
    {
        return 'sylius_admin_channel';
    }
}