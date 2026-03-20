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
namespace Sylius\Behat\Service\Converter;

use Api_Platform\Metadata\Iri_Converter_Interface as BaseIriConverterInterface;
use Api_Platform\Metadata\Operation;
use Api_Platform\Metadata\Url_Generator_Interface;
interface Iri_Converter_Interface extends Base_Iri_Converter_Interface
{
    public function get_iri_from_resource_in_section(object|string $resource, string $section, int $reference_type = Url_Generator_Interface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string;
}