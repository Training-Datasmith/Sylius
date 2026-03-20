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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Bundle\Addressing_Bundle\Form\Event_Listener\Build_Address_Form_Subscriber;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Address_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Country_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Country_Code_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Country_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Code_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Province_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Code_Choice_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Member_Type;
use Sylius\Bundle\Addressing_Bundle\Form\Type\Zone_Type;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('sylius.form.type.address.validation_groups', ['sylius']);
    $parameters->set('sylius.form.type.country.validation_groups', ['sylius']);
    $parameters->set('sylius.form.type.province.validation_groups', ['sylius']);
    $parameters->set('sylius.form.type.zone.validation_groups', ['sylius']);
    $parameters->set('sylius.form.type.zone_member.validation_groups', ['sylius']);
    $services->set('sylius.form.type.address', Address_Type::class)->args(['%sylius.model.address.class%', '%sylius.form.type.address.validation_groups%', inline_service(Build_Address_Form_Subscriber::class)->args([service('sylius.repository.country'), service('form.factory')])])->tag('form.type');
    $services->set('sylius.form.type.country', Country_Type::class)->args(['%sylius.model.country.class%', '%sylius.form.type.country.validation_groups%'])->tag('form.type');
    $services->set('sylius.form.type.country_choice', Country_Choice_Type::class)->args([service('sylius.repository.country')])->tag('form.type');
    $services->set('sylius.form.type.country_code_choice', Country_Code_Choice_Type::class)->args([service('sylius.repository.country')])->tag('form.type');
    $services->set('sylius.form.type.province', Province_Type::class)->args(['%sylius.model.province.class%', '%sylius.form.type.province.validation_groups%'])->tag('form.type');
    $services->set('sylius.form.type.province_choice', Province_Choice_Type::class)->args([service('sylius.repository.province')])->tag('form.type');
    $services->set('sylius.form.type.province_code_choice', Province_Code_Choice_Type::class)->args([service('sylius.repository.province')])->tag('form.type');
    $services->set('sylius.form.type.zone', Zone_Type::class)->args(['%sylius.model.zone.class%', '%sylius.form.type.zone.validation_groups%', '%sylius.scope.zone%'])->tag('form.type');
    $services->set('sylius.form.type.zone_choice', Zone_Choice_Type::class)->args([service('sylius.repository.zone'), '%sylius.scope.zone%'])->tag('form.type');
    $services->set('sylius.form.type.zone_code_choice', Zone_Code_Choice_Type::class)->args([service('sylius.repository.zone')])->tag('form.type');
    $services->set('sylius.form.type.zone_member', Zone_Member_Type::class)->args(['%sylius.model.zone_member.class%', '%sylius.form.type.zone_member.validation_groups%'])->tag('form.type');
};