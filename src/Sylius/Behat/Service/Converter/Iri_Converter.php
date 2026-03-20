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
use Api_Platform\Metadata\Util\Class_Info_Trait;
use Sylius\Bundle\Api_Bundle\Resolver\Operation_Resolver_Interface;
final readonly class Iri_Converter implements Iri_Converter_Interface
{
    use Class_Info_Trait;
    public function __construct(private Base_Iri_Converter_Interface $decorated_iri_converter, private Operation_Resolver_Interface $operation_resolver)
    {
    }
    public function get_resource_from_iri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        return $this->decorated_iri_converter->get_resource_from_iri($iri, $context, $operation);
    }
    public function get_iri_from_resource(object|string $resource, int $reference_type = Url_Generator_Interface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        return $this->decorated_iri_converter->get_iri_from_resource($resource, $reference_type, $operation, $context);
    }
    public function get_iri_from_resource_in_section(object|string $resource, string $section, int $reference_type = Url_Generator_Interface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        $resource_class = $context['force_resource_class'] ?? (\is_string($resource) ? $resource : $this->get_object_class($resource));
        $operation = $this->operation_resolver->resolve($resource_class, $section, $operation);
        return $this->decorated_iri_converter->get_iri_from_resource($resource, $reference_type, $operation, $context);
    }
}