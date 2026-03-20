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
namespace Sylius\Bundle\Admin_Bundle\Form\Data_Transformer;

use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Exception\Transformation_Failed_Exception;
use Symfony\Component\Property_Access\Property_Access;
use Webmozart\Assert\Assert;
/**
 * @see \Sylius\Bundle\ResourceBundle\Form\DataTransformer\ResourceToIdentifierTransformer
 *
 * @implements DataTransformerInterface<ResourceInterface, int|string|ResourceInterface>
 */
final readonly class Resource_To_Identifier_Transformer implements Data_Transformer_Interface
{
    /** @phpstan-ignore-next-line */
    public function __construct(private Repository_Interface $repository, private string $identifier = 'id')
    {
    }
    /**
     * @psalm-suppress MissingParamType
     *
     * @param object|null $value
     */
    public function transform(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }
        /** @psalm-suppress ArgumentTypeCoercion */
        Assert::is_instance_of($value, $this->repository->get_class_name());
        return Property_Access::create_property_accessor()->get_value($value, $this->identifier);
    }
    /** @param int|string|ResourceInterface|null $value */
    public function reverse_transform($value): ?Resource_Interface
    {
        if (null === $value) {
            return null;
        }
        // Early return in case we're already dealing with a resource
        if ($value instanceof Resource_Interface) {
            return $value;
        }
        /** @var ResourceInterface|null $resource */
        $resource = $this->repository->find_one_by([$this->identifier => $value]);
        if (null === $resource) {
            throw new Transformation_Failed_Exception(sprintf('Object "%s" with identifier "%s"="%s" does not exist.', $this->repository->get_class_name(), $this->identifier, $value));
        }
        return $resource;
    }
}