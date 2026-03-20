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
namespace Sylius\Bundle\Admin_Bundle\Doctrine\Query\Taxon;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Entity_Manager_Interface;
use Sylius\Component\Locale\Context\Locale_Context_Interface;
use Sylius\Component\Locale\Context\Locale_Not_Found_Exception;
use Sylius\Component\Resource\Translation\Provider\Translation_Locale_Provider_Interface;
final readonly class All_Taxons implements All_Taxons_Interface
{
    public function __construct(private Entity_Manager_Interface $entity_manager, private Locale_Context_Interface $locale_context, private Translation_Locale_Provider_Interface $translation_locale_provider)
    {
    }
    public function get_array_result(): array
    {
        $fallback_locale = $this->translation_locale_provider->get_default_locale_code();
        try {
            $current_locale = $this->locale_context->get_locale_code();
        } catch (Locale_Not_Found_Exception) {
            $current_locale = $fallback_locale;
        }
        $query_builder = $this->entity_manager->get_connection()->create_query_builder();
        $query_builder->select(['taxon.id as id', 'taxon.tree_root as tree_root', 'taxon.parent_id as parent_id', 'taxon.code as code', 'taxon.tree_left as tree_left', 'taxon.tree_right as tree_right', 'taxon.tree_level as tree_level', 'taxon.position as position', 'taxon.enabled as enabled', 'COALESCE(current_translation.name, fallback_translation.name) as name'])->from('sylius_taxon', 'taxon')->left_join('taxon', 'sylius_taxon_translation', 'current_translation', (string) $query_builder->expr()->and($query_builder->expr()->eq('current_translation.translatable_id', 'taxon.id'), $query_builder->expr()->eq('current_translation.locale', ':currentLocale')))->left_join('taxon', 'sylius_taxon_translation', 'fallback_translation', (string) $query_builder->expr()->and($query_builder->expr()->eq('fallback_translation.translatable_id', 'taxon.id'), $query_builder->expr()->eq('fallback_translation.locale', ':fallbackLocale')))->order_by('taxon.tree_level', Criteria::DESC)->add_order_by('taxon.position', Criteria::ASC)->set_parameter('currentLocale', $current_locale, Types::STRING)->set_parameter('fallbackLocale', $fallback_locale, Types::STRING);
        return $query_builder->execute_query()->fetch_all_associative();
    }
}